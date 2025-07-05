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
     * Search for posts based on the given parameters.
     *
     * @param  array  $params
     * @return array
     * @throws \Exception
     */
    public function searchPosts(array $params): array
    {
        $defaultParams = [
            'query' => '',
            'location' => '',
            'page' => 1,
            'limit' => 10,
            'post_type' => '',
            'date_posted' => 'month', // day, 3days, week, month, anytime
            'sort_by' => 'relevance', // relevance, date
            'radius' => 25, // in miles
            'include_company_posts' => false,
        ];

        $searchParams = array_merge($defaultParams, $params);
        
        try {
            $response = $this->client->post('/posts/search', [
                'json' => [
                    'api_key' => $this->apiKey,
                    'query' => $searchParams['query'],
                    'location' => $searchParams['location'],
                    'page' => $searchParams['page'],
                    'limit' => $searchParams['limit'],
                    'post_type' => $searchParams['post_type'],
                    'date_posted' => $searchParams['date_posted'],
                    'sort_by' => $searchParams['sort_by'],
                    'radius' => $searchParams['radius'],
                    'include_company_posts' => $searchParams['include_company_posts'],
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->formatPostResults($data);
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $searchParams,
            ]);
            
            throw new \Exception('Failed to fetch posts from Tavily API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get post details by ID.
     *
     * @param  string  $postId
     * @return array
     * @throws \Exception
     */
    public function getPostDetails(string $postId): array
    {
        try {
            $response = $this->client->get("/posts/{$postId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'post_id' => $postId,
            ]);
            
            throw new \Exception('Failed to fetch post details from Tavily API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Format post results from Tavily API to a standardized format.
     *
     * @param  array  $apiResponse
     * @return array
     */
    protected function formatPostResults(array $apiResponse): array
    {
        $formattedPosts = [];
        
        if (empty($apiResponse['posts'])) {
            return [];
        }

        foreach ($apiResponse['posts'] as $post) {
            $formattedPosts[] = [
                'external_id' => $post['id'] ?? null,
                'title' => $post['title'] ?? 'No Title',
                'description' => $this->cleanHtml($post['description'] ?? ''),
                'company_name' => $post['company'] ?? 'Unknown Company',
                'company_website' => $post['company_url'] ?? null,
                'company_logo_url' => $post['company_logo'] ?? null,
                'location' => $post['location'] ?? 'Remote',
                'is_remote' => $this->isRemotePost($post),
                'post_type' => $this->mapPostType($post['post_type'] ?? ''),
                'salary_min' => $post['salary_min'] ?? null,
                'salary_max' => $post['salary_max'] ?? null,
                'salary_currency' => $post['salary_currency'] ?? 'USD',
                'salary_period' => $this->mapSalaryPeriod($post['salary_period'] ?? ''),
                'salary_is_estimate' => $post['salary_is_estimate'] ?? true,
                'skills' => $post['skills'] ?? [],
                'categories' => $post['categories'] ?? [],
                'apply_url' => $post['apply_url'] ?? $post['url'] ?? null,
                'post_url' => $post['url'] ?? null,
                'posted_at' => $post['posted_date'] ?? null,
                'expires_at' => $post['expiry_date'] ?? null,
                'is_active' => true,
                'raw_data' => $post,
            ];
        }

        return [
            'posts' => $formattedPosts,
            'total' => $apiResponse['total'] ?? count($formattedPosts),
            'page' => $apiResponse['page'] ?? 1,
            'total_pages' => $apiResponse['total_pages'] ?? 1,
        ];
    }

    /**
     * Check if the post is remote based on available data.
     *
     * @param  array  $post
     * @return bool
     */
    protected function isRemotePost(array $post): bool
    {
        if (isset($post['is_remote'])) {
            return (bool) $post['is_remote'];
        }

        $location = strtolower($post['location'] ?? '');
        $title = strtolower($post['title'] ?? '');
        
        return str_contains($location, 'remote') || 
               str_contains($title, 'remote') ||
               str_contains($location, 'anywhere') ||
               str_contains($post['description'] ?? '', 'work from home') ||
               str_contains($post['description'] ?? '', 'remote work');
    }

    /**
     * Map post type from Tavily to our standard format.
     *
     * @param  string  $postType
     * @return string
     */
    protected function mapPostType(string $postType): string
    {
        $postType = strtolower(trim($postType));
        
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

        return $mapping[$postType] ?? 'full-time';
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
     * Clean HTML from post descriptions.
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
