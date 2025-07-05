<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostBoard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostScraperService
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
     * The post board model.
     *
     * @var \App\Models\PostBoard|null
     */
    protected $postBoard;

    /**
     * Create a new post scraper service instance.
     *
     * @param  string|null  $apiKey
     * @param  \App\Models\PostBoard|null  $postBoard
     * @return void
     */
    public function __construct(?string $apiKey = null, ?PostBoard $postBoard = null)
    {
        $this->tavilyApiKey = $apiKey ?: config('services.tavily.api_key');
        $this->postBoard = $postBoard;
    }

    /**
     * Scrape posts based on search criteria.
     *
     * @param  array  $criteria
     * @param  int  $maxResults
     * @return array
     * @throws \Exception
     */
    public function scrapePosts(array $criteria, int $maxResults = 50): array
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

        // Add post title
        if (!empty($criteria['post_title'])) {
            $queryParts[] = $criteria['post_title'];
        }

        // Add company name
        if (!empty($criteria['company_name'])) {
            $queryParts[] = $criteria['company_name'];
        }

        // Add location
        if (!empty($criteria['location'])) {
            $queryParts[] = $criteria['location'];
        }

        // Add post type if specified
        if (!empty($criteria['post_type'])) {
            $queryParts[] = $criteria['post_type'] . ' post';
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
            'include_domains' => $this->getPostBoardDomains(),
            'exclude_domains' => $this->getExcludedDomains(),
        ]);

        if ($response->failed()) {
            throw new \Exception('Tavily API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get post board domains to include in the search.
     *
     * @return array
     */
    protected function getPostBoardDomains(): array
    {
        if ($this->postBoard) {
            return [$this->postBoard->domain];
        }

        // Default post board domains to include
        return [
            'linkedin.com/posts',
            'indeed.com',
            'glassdoor.com',
            'monster.com',
            'careerbuilder.com',
            'dice.com',
            'ziprecruiter.com',
            'simplyhired.com',
            'angel.co',
            'stackoverflow.com/posts',
            'github.com/posts',
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
     * Process search results and extract post listings.
     *
     * @param  array  $response
     * @param  array  $criteria
     * @return array
     */
    protected function processSearchResults(array $response, array $criteria): array
    {
        $posts = [];
        $processedUrls = [];

        // Process organic results
        if (!empty($response['organic_results'])) {
            foreach ($response['organic_results'] as $result) {
                try {
                    $post = $this->extractPostFromResult($result, $criteria);
                    
                    // Skip if we've already processed this URL
                    if (in_array($post['post_url'], $processedUrls, true)) {
                        continue;
                    }
                    
                    $posts[] = $post;
                    $processedUrls[] = $post['post_url'];
                } catch (\Exception $e) {
                    Log::error('Error processing post result: ' . $e->getMessage(), [
                        'result' => $result,
                        'exception' => $e,
                    ]);
                    continue;
                }
            }
        }

        return $posts;
    }

    /**
     * Extract post data from a search result.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return array
     */
    protected function extractPostFromResult(array $result, array $criteria): array
    {
        // Extract basic information
        $postData = [
            'title' => $result['title'] ?? 'No Title',
            'company_name' => $this->extractCompanyName($result),
            'location' => $this->extractLocation($result, $criteria),
            'post_url' => $result['url'] ?? '',
            'apply_url' => $result['url'] ?? '',
            'description' => $this->cleanDescription($result['content'] ?? ''),
            'posted_at' => now(),
            'source' => $this->extractSource($result['url'] ?? ''),
            'is_remote' => $this->isRemotePost($result, $criteria),
            'post_type' => $criteria['post_type'] ?? null,
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
            $postData['posted_at'] = $metadata['published_date'] ?? now();
            
            if (!empty($metadata['salary'])) {
                $salaryData = $this->extractSalary($metadata['salary']);
                $postData = array_merge($postData, $salaryData);
            }
        }

        return $postData;
    }

    /**
     * Extract company name from search result.
     *
     * @param  array  $result
     * @return string
     */
    protected function extractCompanyName(array $result): string
    {
        // Try to get company name from the title (common format: "Post Title at Company")
        if (!empty($result['title'])) {
            $titleParts = explode(' at ', $result['title']);
            if (count($titleParts) > 1) {
                return trim($titleParts[1]);
            }
            
            // Try another common format: "Company: Post Title"
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
        
        // Try to extract from title (common format: "Post Title - Location")
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
     * Check if post is remote based on result and criteria.
     *
     * @param  array  $result
     * @param  array  $criteria
     * @return bool
     */
    protected function isRemotePost(array $result, array $criteria): bool
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
     * Clean and format post description.
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
     * Save scraped posts to the database.
     *
     * @param  array  $posts
     * @param  int  $userId
     * @return array
     */
    public function savePosts(array $posts, int $userId): array
    {
        $savedPosts = [];
        $skipped = 0;
        
        foreach ($posts as $postData) {
            try {
                // Skip if post with the same URL already exists
                if (Post::where('post_url', $postData['post_url'])->exists()) {
                    $skipped++;
                    continue;
                }
                
                // Create new post
                $post = new Post($postData);
                $post->user_id = $userId;
                $post->post_board_id = $this->postBoard ? $this->postBoard->id : null;
                $post->save();
                
                $savedPosts[] = $post;
            } catch (\Exception $e) {
                Log::error('Error saving post: ' . $e->getMessage(), [
                    'post_data' => $postData,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return [
            'saved' => count($savedPosts),
            'skipped' => $skipped,
            'posts' => $savedPosts,
        ];
    }

    /**
     * Set the post board to use for scraping.
     *
     * @param  \App\Models\PostBoard  $postBoard
     * @return $this
     */
    public function setPostBoard(PostBoard $postBoard): self
    {
        $this->postBoard = $postBoard;
        return $this;
    }
}
