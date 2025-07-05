<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobBoard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobScraperService
{
    /**
     * The Tavily API base URL.
     *
     * @var string
     */
    protected $tavilyApiUrl = 'https://api.tavily.com';

    /**
     * The Tavily API key.
     *
     * @var string
     */
    protected $tavilyApiKey;

    /**
     * The job board model.
     *
     * @var \App\Models\JobBoard|null
     */
    protected $jobBoard;

    /**
     * Create a new job scraper service instance.
     *
     * @param  string|null  $apiKey
     * @param  \App\Models\JobBoard|null  $jobBoard
     * @return void
     */
    public function __construct(?string $apiKey = null, ?JobBoard $jobBoard = null)
    {
        $this->tavilyApiKey = $apiKey ?: config('services.tavily.api_key');
        $this->jobBoard = $jobBoard;
    }

    /**
     * Scrape jobs based on search criteria.
     *
     * @param  array  $criteria
     * @param  int  $maxResults
     * @return array
     * @throws \Exception
     */
    public function scrapeJobs(array $criteria, int $maxResults = 50): array
    {
        if (empty($this->tavilyApiKey)) {
            throw new \Exception('Tavily API key is not configured.');
        }

        $searchQuery = $this->buildSearchQuery($criteria);
        $response = $this->makeTavilyRequest($searchQuery, $maxResults);

        return $this->processSearchResults($response, $criteria);
    }

    /**
     * Build a search query from criteria.
     *
     * @param  array  $criteria
     * @return string
     */
    protected function buildSearchQuery(array $criteria): string
    {
        $queryParts = [];

        // Add keywords
        if (!empty($criteria['keywords'])) {
            $queryParts[] = $criteria['keywords'];
        }

        // Add job title
        if (!empty($criteria['job_title'])) {
            $queryParts[] = $criteria['job_title'];
        }

        // Add company name
        if (!empty($criteria['company_name'])) {
            $queryParts[] = $criteria['company_name'];
        }

        // Add location
        if (!empty($criteria['location'])) {
            $queryParts[] = $criteria['location'];
        }

        // Add job type if specified
        if (!empty($criteria['job_type'])) {
            $queryParts[] = $criteria['job_type'] . ' job';
        }

        // Add experience level if specified
        if (!empty($criteria['experience_level'])) {
            $queryParts[] = $criteria['experience_level'] . ' level';
        }

        // Add remote filter if specified
        if (!empty($criteria['is_remote'])) {
            $queryParts[] = 'remote';
        }

        return implode(' ', $queryParts);
    }

    /**
     * Make a request to the Tavily API.
     *
     * @param  string  $query
     * @param  int  $maxResults
     * @return array
     * @throws \Exception
     */
    protected function makeTavilyRequest(string $query, int $maxResults): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->tavilyApiKey,
        ])->post("{$this->tavilyApiUrl}/search", [
            'query' => $query,
            'search_depth' => 'advanced',
            'include_answer' => false,
            'include_raw_content' => true,
            'max_results' => $maxResults,
            'include_domains' => $this->getJobBoardDomains(),
            'exclude_domains' => $this->getExcludedDomains(),
        ]);

        if ($response->failed()) {
            throw new \Exception('Tavily API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get job board domains to include in the search.
     *
     * @return array
     */
    protected function getJobBoardDomains(): array
    {
        if ($this->jobBoard) {
            return [$this->jobBoard->domain];
        }

        // Default job board domains to include
        return [
            'linkedin.com/jobs',
            'indeed.com',
            'glassdoor.com',
            'monster.com',
            'careerbuilder.com',
            'dice.com',
            'ziprecruiter.com',
            'simplyhired.com',
            'angel.co',
            'stackoverflow.com/jobs',
            'github.com/jobs',
            'remoteok.io',
            'weworkremotely.com',
        ];
    }

    /**
     * Get domains to exclude from search results.
     *
     * @return array
     */
    protected function getExcludedDomains(): array
    {
        return [
            'facebook.com',
            'twitter.com',
            'instagram.com',
            'youtube.com',
            'pinterest.com',
            'tiktok.com',
        ];
    }

    /**
     * Process search results and extract job listings.
     *
     * @param  array  $response
     * @param  array  $criteria
     * @return array
     */
    protected function processSearchResults(array $response, array $criteria): array
    {
        $jobs = [];
        $processedUrls = [];

        // Process organic results
        if (!empty($response['organic_results'])) {
            foreach ($response['organic_results'] as $result) {
                try {
                    $job = $this->extractJobFromResult($result, $criteria);
                    
                    // Skip if we've already processed this URL
                    if (in_array($job['job_url'], $processedUrls, true)) {
                        continue;
                    }
                    
                    $jobs[] = $job;
                    $processedUrls[] = $job['job_url'];
                } catch (\Exception $e) {
                    Log::error('Error processing job result: ' . $e->getMessage(), [
                        'result' => $result,
                        'exception' => $e,
                    ]);
                    continue;
                }
            }
        }

        return $jobs;
    }

    /**
     * Extract job data from a search result.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return array
     */
    protected function extractJobFromResult(array $result, array $criteria): array
    {
        // Extract basic information
        $jobData = [
            'title' => $result['title'] ?? 'No Title',
            'company_name' => $this->extractCompanyName($result),
            'location' => $this->extractLocation($result, $criteria),
            'job_url' => $result['url'] ?? '',
            'apply_url' => $result['url'] ?? '',
            'description' => $this->cleanDescription($result['content'] ?? ''),
            'posted_at' => now(),
            'source' => $this->extractSource($result['url'] ?? ''),
            'is_remote' => $this->isRemoteJob($result, $criteria),
            'job_type' => $criteria['job_type'] ?? null,
            'experience_level' => $criteria['experience_level'] ?? null,
            'salary_min' => $criteria['salary_min'] ?? null,
            'salary_max' => $criteria['salary_max'] ?? null,
            'salary_currency' => $criteria['salary_currency'] ?? 'USD',
            'skills' => $this->extractSkills($result, $criteria),
            'raw_data' => json_encode($result),
        ];

        // Extract additional metadata if available
        if (!empty($result['metadata'])) {
            $metadata = $result['metadata'];
            $jobData['posted_at'] = $metadata['published_date'] ?? now();
            
            if (!empty($metadata['salary'])) {
                $salaryData = $this->extractSalary($metadata['salary']);
                $jobData = array_merge($jobData, $salaryData);
            }
        }

        return $jobData;
    }

    /**
     * Extract company name from search result.
     *
     * @param  array  $result
     * @return string
     */
    protected function extractCompanyName(array $result): string
    {
        // Try to get company name from the title (common format: "Job Title at Company")
        if (!empty($result['title'])) {
            $titleParts = explode(' at ', $result['title']);
            if (count($titleParts) > 1) {
                return trim($titleParts[1]);
            }
            
            // Try another common format: "Company: Job Title"
            $titleParts = explode(': ', $result['title']);
            if (count($titleParts) > 1) {
                return trim($titleParts[0]);
            }
        }
        
        // Fall back to domain name if available
        if (!empty($result['url'])) {
            $domain = parse_url($result['url'], PHP_URL_HOST);
            if ($domain) {
                // Remove www. and .com parts
                return ucfirst(preg_replace('/^www\.|\.(com|org|net|io|co\.\w{2,})$/i', '', $domain));
            }
        }
        
        return 'Unknown Company';
    }

    /**
     * Extract location from search result.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return string
     */
    protected function extractLocation(array $result, array $criteria): string
    {
        // If location is in criteria, use that
        if (!empty($criteria['location'])) {
            return $criteria['location'];
        }
        
        // Try to extract from title (common format: "Job Title - Location")
        if (!empty($result['title'])) {
            $titleParts = explode(' - ', $result['title']);
            if (count($titleParts) > 1) {
                $possibleLocation = trim(end($titleParts));
                // Basic check if this looks like a location
                if (preg_match('/[A-Z][a-z]+,?\s+[A-Z]{2}|Remote|Worldwide|Anywhere/i', $possibleLocation)) {
                    return $possibleLocation;
                }
            }
        }
        
        // Try to extract from snippet
        if (!empty($result['content'])) {
            // Look for common location patterns in the content
            if (preg_match('/(Remote|Worldwide|Anywhere|[A-Z][a-z]+(?:[\s-][A-Z][a-z]+)*,\s*[A-Z]{2})/i', $result['content'], $matches)) {
                return $matches[0];
            }
        }
        
        return 'Location not specified';
    }

    /**
     * Check if job is remote based on result and criteria.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return bool
     */
    protected function isRemoteJob(array $result, array $criteria): bool
    {
        // If remote is specified in criteria, use that
        if (isset($criteria['is_remote'])) {
            return (bool) $criteria['is_remote'];
        }
        
        // Check title and content for remote indicators
        $remoteKeywords = ['remote', 'work from home', 'wfh', 'virtual', 'telecommute'];
        
        $textToCheck = strtolower($result['title'] . ' ' . ($result['content'] ?? ''));
        
        foreach ($remoteKeywords as $keyword) {
            if (str_contains($textToCheck, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Extract skills from search result.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return array
     */
    protected function extractSkills(array $result, array $criteria): array
    {
        $skills = [];
        
        // Add skills from criteria if provided
        if (!empty($criteria['skills']) && is_array($criteria['skills'])) {
            $skills = array_merge($skills, $criteria['skills']);
        }
        
        // Try to extract skills from the title and content
        $textToCheck = strtolower($result['title'] . ' ' . ($result['content'] ?? ''));
        
        // Common programming languages and technologies
        $commonSkills = [
            'JavaScript', 'Python', 'Java', 'C#', 'PHP', 'C++', 'TypeScript', 'Ruby', 'Swift', 'Kotlin',
            'React', 'Angular', 'Vue.js', 'Node.js', 'Django', 'Spring', 'Laravel', 'Ruby on Rails',
            'AWS', 'Azure', 'Google Cloud', 'Docker', 'Kubernetes', 'Terraform',
            'SQL', 'MongoDB', 'PostgreSQL', 'MySQL', 'Redis', 'Elasticsearch',
            'Git', 'CI/CD', 'DevOps', 'Agile', 'Scrum',
        ];
        
        foreach ($commonSkills as $skill) {
            if (str_contains($textToCheck, strtolower($skill))) {
                $skills[] = $skill;
            }
        }
        
        return array_unique($skills);
    }

    /**
     * Extract salary information from text.
     *
     * @param  string  $salaryText
     * @return array
     */
    protected function extractSalary(string $salaryText): array
    {
        $result = [
            'salary_min' => null,
            'salary_max' => null,
            'salary_currency' => 'USD',
        ];
        
        // Match salary patterns like "$80,000 - $120,000 per year"
        if (preg_match('/\$?([\d,]+)\s*-\s*\$?([\d,]+)/', $salaryText, $matches)) {
            $result['salary_min'] = (float) str_replace(',', '', $matches[1]);
            $result['salary_max'] = (float) str_replace(',', '', $matches[2]);
        } 
        // Match single salary value like "$100,000 per year"
        elseif (preg_match('/\$?([\d,]+)/', $salaryText, $matches)) {
            $salary = (float) str_replace(',', '', $matches[1]);
            $result['salary_min'] = $salary * 0.9; // Estimate min as 90% of the value
            $result['salary_max'] = $salary * 1.1; // Estimate max as 110% of the value
        }
        
        // Detect currency (simplified)
        if (str_contains($salaryText, '£')) {
            $result['salary_currency'] = 'GBP';
        } elseif (str_contains($salaryText, '€')) {
            $result['salary_currency'] = 'EUR';
        } elseif (str_contains($salaryText, '₹')) {
            $result['salary_currency'] = 'INR';
        }
        
        return $result;
    }

    /**
     * Extract source domain from URL.
     *
     * @param  string  $url
     * @return string
     */
    protected function extractSource(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            return 'Unknown';
        }
        
        // Remove www. prefix
        return preg_replace('/^www\./i', '', $domain);
    }

    /**
     * Clean and format job description.
     *
     * @param  string  $description
     * @return string
     */
    protected function cleanDescription(string $description): string
    {
        // Remove excessive whitespace
        $description = preg_replace('/\s+/', ' ', $description);
        
        // Remove HTML tags
        $description = strip_tags($description);
        
        // Truncate if too long
        if (strlen($description) > 10000) {
            $description = substr($description, 0, 10000) . '...';
        }
        
        return trim($description);
    }

    /**
     * Save scraped jobs to the database.
     *
     * @param  array  $jobs
     * @param  int  $userId
     * @return array
     */
    public function saveJobs(array $jobs, int $userId): array
    {
        $savedJobs = [];
        $skipped = 0;
        
        foreach ($jobs as $jobData) {
            try {
                // Skip if job with the same URL already exists
                if (Job::where('job_url', $jobData['job_url'])->exists()) {
                    $skipped++;
                    continue;
                }
                
                // Create new job
                $job = new Job($jobData);
                $job->user_id = $userId;
                $job->job_board_id = $this->jobBoard ? $this->jobBoard->id : null;
                $job->save();
                
                $savedJobs[] = $job;
            } catch (\Exception $e) {
                Log::error('Error saving job: ' . $e->getMessage(), [
                    'job_data' => $jobData,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return [
            'saved' => count($savedJobs),
            'skipped' => $skipped,
            'jobs' => $savedJobs,
        ];
    }

    /**
     * Set the job board to use for scraping.
     *
     * @param  \App\Models\JobBoard  $jobBoard
     * @return $this
     */
    public function setJobBoard(JobBoard $jobBoard): self
    {
        $this->jobBoard = $jobBoard;
        return $this;
    }
}
