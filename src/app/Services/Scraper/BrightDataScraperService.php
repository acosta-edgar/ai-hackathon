<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\GeminiApiService;
use App\Models\Job;
use App\Models\Company;
use Exception;

class BrightDataScraperService
{
    protected string $apiUrl;
    protected string $username;
    protected string $password;
    protected string $customerId;
    protected GeminiApiService $geminiService;

    public function __construct(GeminiApiService $geminiService)
    {
        $this->apiUrl = config('services.bright_data.api_url');
        $this->username = config('services.bright_data.username');
        $this->password = config('services.bright_data.password');
        $this->customerId = config('services.bright_data.customer_id');
        $this->geminiService = $geminiService;
    }

    /**
     * Search for jobs on LinkedIn using Bright Data
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function searchJobs(array $params): array
    {
        $defaultParams = [
            'query' => '',
            'location' => '',
            'country' => 'us',
            'page' => 1,
            'limit' => 10,
            'filters' => []
        ];

        $params = array_merge($defaultParams, $params);

        $payload = [
            'customer' => $this->customerId,
            'zone' => 'linkedin',
            'entity' => 'search',
            'query' => $params['query'],
            'location' => $params['location'],
            'country' => $params['country'],
            'page' => $params['page'],
            'limit' => $params['limit'],
            'filters' => $params['filters']
        ];

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->apiUrl}/scraper/linkedin/search", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processJobSearchResults($data);
            }

            throw new Exception("Bright Data API error: " . $response->body());
        } catch (\Exception $e) {
            Log::error('Bright Data job search failed', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Process and normalize job search results
     *
     * @param array $data
     * @return array
     */
    protected function processJobSearchResults(array $data): array
    {
        $jobs = [];
        
        if (empty($data['results'])) {
            return [];
        }

        foreach ($data['results'] as $result) {
            $job = [
                'external_id' => $result['job_id'] ?? null,
                'title' => $result['title'] ?? 'No title',
                'company_name' => $result['company']['name'] ?? 'Unknown Company',
                'company_logo' => $result['company']['logo'] ?? null,
                'location' => $this->formatLocation($result),
                'job_type' => $this->mapJobType($result['job_type'] ?? []),
                'salary' => $this->formatSalary($result),
                'description' => $result['description'] ?? '',
                'posted_at' => $this->parseDate($result['posted_at'] ?? null),
                'url' => $result['url'] ?? null,
                'source' => 'linkedin',
                'raw_data' => json_encode($result),
                'metadata' => [
                    'seniority_level' => $result['seniority_level'] ?? null,
                    'employment_type' => $result['employment_type'] ?? null,
                    'job_function' => $result['job_function'] ?? null,
                    'industries' => $result['industries'] ?? [],
                ]
            ];

            $jobs[] = $job;
        }

        return $jobs;
    }

    /**
     * Get detailed job information
     *
     * @param string $jobId
     * @return array
     * @throws \Exception
     */
    public function getJobDetails(string $jobId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->apiUrl}/scraper/linkedin/job/{$jobId}", [
                    'customer' => $this->customerId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processJobDetails($data);
            }

            throw new Exception("Bright Data API error: " . $response->body());
        } catch (\Exception $e) {
            Log::error('Bright Data job details fetch failed', [
                'error' => $e->getMessage(),
                'job_id' => $jobId
            ]);
            throw $e;
        }
    }

    /**
     * Process and normalize job details
     *
     * @param array $data
     * @return array
     */
    protected function processJobDetails(array $data): array
    {
        return [
            'external_id' => $data['job_id'] ?? null,
            'title' => $data['title'] ?? 'No title',
            'company' => $this->processCompanyInfo($data['company'] ?? []),
            'location' => $this->formatLocation($data),
            'job_type' => $this->mapJobType($data['job_type'] ?? []),
            'salary' => $this->formatSalary($data),
            'description' => $data['description'] ?? '',
            'requirements' => $data['requirements'] ?? [],
            'responsibilities' => $data['responsibilities'] ?? [],
            'benefits' => $data['benefits'] ?? [],
            'posted_at' => $this->parseDate($data['posted_at'] ?? null),
            'expires_at' => $this->parseDate($data['expires_at'] ?? null),
            'url' => $data['url'] ?? null,
            'source' => 'linkedin',
            'raw_data' => json_encode($data),
            'metadata' => [
                'seniority_level' => $data['seniority_level'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'job_function' => $data['job_function'] ?? null,
                'industries' => $data['industries'] ?? [],
                'applicants' => $data['applicants'] ?? null,
                'applications' => $data['applications'] ?? null,
            ]
        ];
    }

    /**
     * Process company information
     *
     * @param array $companyData
     * @return array
     */
    protected function processCompanyInfo(array $companyData): array
    {
        return [
            'name' => $companyData['name'] ?? 'Unknown Company',
            'logo' => $companyData['logo'] ?? null,
            'url' => $companyData['url'] ?? null,
            'industry' => $companyData['industry'] ?? null,
            'company_size' => $companyData['company_size'] ?? null,
            'headquarters' => $companyData['headquarters'] ?? null,
            'founded' => $companyData['founded'] ?? null,
            'description' => $companyData['description'] ?? null,
            'website' => $companyData['website'] ?? null,
        ];
    }

    /**
     * Get company information from LinkedIn
     *
     * @param string $companyId LinkedIn company ID or URL
     * @return array
     * @throws \Exception
     */
    public function getCompanyInfo(string $companyId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->apiUrl}/scraper/linkedin/company/{$companyId}", [
                    'customer' => $this->customerId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processCompanyInfo($data);
            }

            throw new Exception("Bright Data API error: " . $response->body());
        } catch (\Exception $e) {
            Log::error('Bright Data company info fetch failed', [
                'error' => $e->getMessage(),
                'company_id' => $companyId
            ]);
            throw $e;
        }
    }

    /**
     * Get similar jobs for a given job ID
     *
     * @param string $jobId
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getSimilarJobs(string $jobId, int $limit = 5): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->apiUrl}/scraper/linkedin/job/{$jobId}/similar", [
                    'customer' => $this->customerId,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processJobSearchResults($data);
            }

            throw new Exception("Bright Data API error: " . $response->body());
        } catch (\Exception $e) {
            Log::error('Bright Data similar jobs fetch failed', [
                'error' => $e->getMessage(),
                'job_id' => $jobId
            ]);
            throw $e;
        }
    }

    /**
     * Format location from job data
     *
     * @param array $jobData
     * @return string
     */
    protected function formatLocation(array $jobData): string
    {
        $location = [];
        
        if (!empty($jobData['location'])) {
            if (is_string($jobData['location'])) {
                return $jobData['location'];
            }
            
            if (is_array($jobData['location'])) {
                if (!empty($jobData['location']['city'])) {
                    $location[] = $jobData['location']['city'];
                }
                if (!empty($jobData['location']['state'])) {
                    $location[] = $jobData['location']['state'];
                }
                if (!empty($jobData['location']['country'])) {
                    $location[] = $jobData['location']['country'];
                }
            }
        }
        
        return !empty($location) ? implode(', ', $location) : 'Remote';
    }

    /**
     * Format salary information
     *
     * @param array $jobData
     * @return array|null
     */
    protected function formatSalary(array $jobData): ?array
    {
        if (empty($jobData['salary'])) {
            return null;
        }

        return [
            'min' => $jobData['salary']['min'] ?? null,
            'max' => $jobData['salary']['max'] ?? null,
            'currency' => $jobData['salary']['currency'] ?? 'USD',
            'period' => $jobData['salary']['period'] ?? 'year',
            'is_estimate' => $jobData['salary']['is_estimate'] ?? true,
        ];
    }

    /**
     * Map job types to standardized format
     *
     * @param array $jobTypes
     * @return array
     */
    protected function mapJobType(array $jobTypes): array
    {
        $mapped = [];
        $typeMap = [
            'full-time' => 'full_time',
            'part-time' => 'part_time',
            'contract' => 'contract',
            'temporary' => 'temporary',
            'internship' => 'internship',
            'volunteer' => 'volunteer',
            'freelance' => 'freelance',
            'permanent' => 'permanent',
        ];

        foreach ($jobTypes as $type) {
            $normalized = strtolower(trim($type));
            if (isset($typeMap[$normalized])) {
                $mapped[] = $typeMap[$normalized];
            } else {
                $mapped[] = $normalized;
            }
        }

        return array_unique($mapped);
    }

    /**
     * Parse date string to DateTime
     *
     * @param string|null $dateString
     * @return string|null
     */
    protected function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return (new \DateTime($dateString))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning('Failed to parse date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Enrich job data with AI analysis
     *
     * @param array $jobData
     * @return array
     */
    public function enrichWithAiAnalysis(array $jobData): array
    {
        try {
            $prompt = $this->buildJobAnalysisPrompt($jobData);
            $analysis = $this->geminiService->generateText($prompt);
            
            $jobData['ai_analysis'] = [
                'summary' => $analysis['summary'] ?? null,
                'key_skills' => $analysis['key_skills'] ?? [],
                'seniority_level' => $analysis['seniority_level'] ?? null,
                'company_culture' => $analysis['company_culture'] ?? null,
                'growth_opportunities' => $analysis['growth_opportunities'] ?? null,
                'generated_at' => now()->toDateTimeString(),
            ];
            
            return $jobData;
        } catch (\Exception $e) {
            Log::error('Failed to enrich job with AI analysis', [
                'error' => $e->getMessage(),
                'job_id' => $jobData['external_id'] ?? null
            ]);
            
            // Return original data if enrichment fails
            return $jobData;
        }
    }

    /**
     * Build prompt for job analysis
     *
     * @param array $jobData
     * @return string
     */
    protected function buildJobAnalysisPrompt(array $jobData): string
    {
        $prompt = "Analyze the following job posting and provide a structured analysis. " .
                 "Focus on extracting key information that would be valuable for a job seeker.\n\n" .
                 "Job Title: {$jobData['title']}\n" .
                 "Company: {$jobData['company']['name']}\n" .
                 "Location: {$this->formatLocation($jobData)}\n" .
                 "Job Type: " . implode(', ', $jobData['job_type'] ?? []) . "\n\n" .
                 "Job Description:\n{$jobData['description']}\n\n" .
                 "Please provide analysis in the following JSON structure:\n" .
                 "{\n" .
                 "  \"summary\": \"Brief 2-3 sentence summary of the role and key requirements\",\n" .
                 "  \"key_skills\": [\"list\", \"of\", \"required\", \"and\", \"preferred\", \"skills\"],\n" .
                 "  \"seniority_level\": \"entry|mid|senior|executive\",\n" .
                 "  \"company_culture\": \"Brief insights about the company culture based on the job description\",\n" .
                 "  \"growth_opportunities\": \"Brief analysis of potential growth opportunities\"\n" .
                 "}";

        return $prompt;
    }
}
