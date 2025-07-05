<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TavilyApiService
{
    /**
     * The HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.tavily.com';

    /**
     * The API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new Tavily API service instance.
     *
     * @param  string|null  $apiKey
     * @return void
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey ?: Config::get('services.tavily.api_key');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Search for jobs based on the given parameters.
     *
     * @param  array  $params
     * @return array
     * @throws \Exception
     */
    public function searchJobs(array $params): array
    {
        $defaultParams = [
            'query' => '',
            'location' => '',
            'page' => 1,
            'limit' => 10,
            'job_type' => '',
            'date_posted' => 'month', // day, 3days, week, month, anytime
            'sort_by' => 'relevance', // relevance, date
            'radius' => 25, // in miles
            'include_company_jobs' => false,
        ];

        $searchParams = array_merge($defaultParams, $params);
        
        try {
            $response = $this->client->post('/jobs/search', [
                'json' => [
                    'api_key' => $this->apiKey,
                    'query' => $searchParams['query'],
                    'location' => $searchParams['location'],
                    'page' => $searchParams['page'],
                    'limit' => $searchParams['limit'],
                    'job_type' => $searchParams['job_type'],
                    'date_posted' => $searchParams['date_posted'],
                    'sort_by' => $searchParams['sort_by'],
                    'radius' => $searchParams['radius'],
                    'include_company_jobs' => $searchParams['include_company_jobs'],
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->formatJobResults($data);
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $searchParams,
            ]);
            
            throw new \Exception('Failed to fetch jobs from Tavily API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get job details by ID.
     *
     * @param  string  $jobId
     * @return array
     * @throws \Exception
     */
    public function getJobDetails(string $jobId): array
    {
        try {
            $response = $this->client->get("/jobs/{$jobId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'job_id' => $jobId,
            ]);
            
            throw new \Exception('Failed to fetch job details from Tavily API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Format job results from Tavily API to a standardized format.
     *
     * @param  array  $apiResponse
     * @return array
     */
    protected function formatJobResults(array $apiResponse): array
    {
        $formattedJobs = [];
        
        if (empty($apiResponse['jobs'])) {
            return [];
        }

        foreach ($apiResponse['jobs'] as $job) {
            $formattedJobs[] = [
                'external_id' => $job['id'] ?? null,
                'title' => $job['title'] ?? 'No Title',
                'description' => $this->cleanHtml($job['description'] ?? ''),
                'company_name' => $job['company'] ?? 'Unknown Company',
                'company_website' => $job['company_url'] ?? null,
                'company_logo_url' => $job['company_logo'] ?? null,
                'location' => $job['location'] ?? 'Remote',
                'is_remote' => $this->isRemoteJob($job),
                'job_type' => $this->mapJobType($job['job_type'] ?? ''),
                'salary_min' => $job['salary_min'] ?? null,
                'salary_max' => $job['salary_max'] ?? null,
                'salary_currency' => $job['salary_currency'] ?? 'USD',
                'salary_period' => $this->mapSalaryPeriod($job['salary_period'] ?? ''),
                'salary_is_estimate' => $job['salary_is_estimate'] ?? true,
                'skills' => $job['skills'] ?? [],
                'categories' => $job['categories'] ?? [],
                'apply_url' => $job['apply_url'] ?? $job['url'] ?? null,
                'job_url' => $job['url'] ?? null,
                'posted_at' => $job['posted_date'] ?? null,
                'expires_at' => $job['expiry_date'] ?? null,
                'is_active' => true,
                'raw_data' => $job,
            ];
        }

        return [
            'jobs' => $formattedJobs,
            'total' => $apiResponse['total'] ?? count($formattedJobs),
            'page' => $apiResponse['page'] ?? 1,
            'total_pages' => $apiResponse['total_pages'] ?? 1,
        ];
    }

    /**
     * Check if the job is remote based on available data.
     *
     * @param  array  $job
     * @return bool
     */
    protected function isRemoteJob(array $job): bool
    {
        if (isset($job['is_remote'])) {
            return (bool) $job['is_remote'];
        }

        $location = strtolower($job['location'] ?? '');
        $title = strtolower($job['title'] ?? '');
        
        return str_contains($location, 'remote') || 
               str_contains($title, 'remote') ||
               str_contains($location, 'anywhere') ||
               str_contains($job['description'] ?? '', 'work from home') ||
               str_contains($job['description'] ?? '', 'remote work');
    }

    /**
     * Map job type from Tavily to our standard format.
     *
     * @param  string  $jobType
     * @return string
     */
    protected function mapJobType(string $jobType): string
    {
        $jobType = strtolower(trim($jobType));
        
        $mapping = [
            'full-time' => 'full-time',
            'full_time' => 'full-time',
            'full time' => 'full-time',
            'part-time' => 'part-time',
            'part_time' => 'part-time',
            'part time' => 'part-time',
            'contract' => 'contract',
            'temporary' => 'temporary',
            'temp' => 'temporary',
            'internship' => 'internship',
            'intern' => 'internship',
            'volunteer' => 'volunteer',
            'freelance' => 'contract',
            'per-diem' => 'per-diem',
            'per_diem' => 'per-diem',
            'per diem' => 'per-diem',
        ];

        return $mapping[$jobType] ?? 'full-time';
    }

    /**
     * Map salary period from Tavily to our standard format.
     *
     * @param  string  $period
     * @return string
     */
    protected function mapSalaryPeriod(string $period): string
    {
        $period = strtolower(trim($period));
        
        $mapping = [
            'year' => 'year',
            'yearly' => 'year',
            'annum' => 'year',
            'month' => 'month',
            'monthly' => 'month',
            'week' => 'week',
            'weekly' => 'week',
            'day' => 'day',
            'daily' => 'day',
            'hour' => 'hour',
            'hourly' => 'hour',
        ];

        return $mapping[$period] ?? 'year';
    }

    /**
     * Clean HTML from job descriptions.
     *
     * @param  string  $html
     * @return string
     */
    protected function cleanHtml(string $html): string
    {
        // Strip HTML tags but preserve line breaks and paragraphs
        $text = strip_tags($html, '<p><br><ul><ol><li><strong><em><b><i><u>');
        
        // Convert HTML entities to their corresponding characters
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Get the remaining API credits.
     *
     * @return array
     * @throws \Exception
     */
    public function getCredits(): array
    {
        try {
            $response = $this->client->get('/credits', [
                'query' => [
                    'api_key' => $this->apiKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error (getCredits): ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            throw new \Exception('Failed to fetch API credits: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
