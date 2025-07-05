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
     * The API base URL for Tavily Crawl API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.tavily.com/crawl';

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
     * Crawl job listings using Tavily Crawl API.
     *
     * @param  array  $params
     * @return array
     * @throws \Exception
     */
    public function searchPosts(array $params): array
    {
        $defaultParams = [
            'urls' => [],
            'crawl_depth' => 1,
            'include_images' => false,
            'include_links' => true,
            'include_screenshot' => false,
            'include_pdf' => false,
            'include_raw_html' => true,
            'include_structured_data' => true,
            'timeout' => 30,
        ];

        $searchParams = array_merge($defaultParams, $params);
        
        if (empty($searchParams['urls'])) {
            throw new \InvalidArgumentException('At least one URL is required for crawling');
        }
        
        try {
            $response = $this->client->post('/crawl', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'json' => array_filter($searchParams, function($value) {
                    return $value !== null && $value !== '' && $value !== [];
                })
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->formatCrawlResults($data);
        } catch (GuzzleException $e) {
            Log::error('Tavily Crawl API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $searchParams,
            ]);
            
            throw new \Exception('Failed to crawl with Tavily API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Format the crawl results from Tavily Crawl API.
     *
     * @param  array  $data
     * @return array
     */
    protected function formatCrawlResults(array $data): array
    {
        if (!isset($data['results']) || !is_array($data['results'])) {
            return [];
        }

        $formattedResults = [];
        
        foreach ($data['results'] as $result) {
            $formattedResults[] = [
                'url' => $result['url'] ?? '#',
                'title' => $result['title'] ?? 'No title',
                'content' => $result['content'] ?? '',
                'html' => $result['html'] ?? '',
                'links' => $result['links'] ?? [],
                'images' => $result['images'] ?? [],
                'screenshot' => $result['screenshot'] ?? null,
                'pdf' => $result['pdf'] ?? null,
                'structured_data' => $result['structured_data'] ?? [],
                'crawled_at' => now()->toDateTimeString(),
                'status' => $result['status'] ?? 'unknown',
            ];
        }

        return [
            'results' => $formattedResults,
            'total' => count($formattedResults),
            'request_id' => $data['request_id'] ?? null,
            'crawl_id' => $data['crawl_id'] ?? null,
        ];
    }

    /**
     * Extract company name from URL if possible.
     *
     * @param  string  $url
     * @return string
     */
    protected function extractCompanyFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $parts = explode('.', $host);
        
        if (count($parts) > 1) {
            // Remove 'www' if present and get the first part of the domain
            $company = $parts[0] === 'www' ? $parts[1] : $parts[0];
            return ucfirst($company);
        }
        
        return 'Unknown Company';
    }

    /**
     * Extract location from result if available.
     *
     * @param  array  $result
     * @return string
     */
    protected function extractLocation(array $result): string
    {
        if (isset($result['metadata']['location'])) {
            return $result['metadata']['location'];
        }
        
        // Try to extract location from content or title
        $text = ($result['content'] ?? '') . ' ' . ($result['title'] ?? '');
        
        // Simple pattern to find common location indicators
        if (preg_match('/(remote|anywhere|work from home|wfh)/i', $text)) {
            return 'Remote';
        }
        
        return 'Location not specified';
    }
    
    /**
     * Check if the job is remote based on available data.
     *
     * @param  array  $job
     * @return bool
     */
    protected function isRemoteJob(array $job): bool
    {
        $text = strtolower(($job['content'] ?? '') . ' ' . ($job['title'] ?? ''));
        
        return str_contains($text, 'remote') || 
               str_contains($text, 'work from home') || 
               str_contains($text, 'wfh') ||
               str_contains($text, 'anywhere in the world') ||
               str_contains($text, 'virtual office');
    }

    /**
     * Get the crawl status for a specific crawl request.
     *
     * @param  string  $crawlId
     * @return array
     * @throws \Exception
     */
    public function getCrawlStatus(string $crawlId): array
    {
        try {
            $response = $this->client->get("/crawl/status/{$crawlId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Tavily Crawl API Error (getCrawlStatus): ' . $e->getMessage(), [
                'exception' => $e,
                'crawl_id' => $crawlId,
            ]);
            
            throw new \Exception('Failed to get crawl status: ' . $e->getMessage(), $e->getCode(), $e);
        }
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
            $response = $this->client->get('https://api.tavily.com/account', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'credits_remaining' => $data['credits_remaining'] ?? 0,
                'plan' => $data['plan'] ?? 'free',
                'crawl_credits' => $data['crawl_credits'] ?? 0,
                'credits_used_this_month' => $data['credits_used_this_month'] ?? 0,
            ];
        } catch (GuzzleException $e) {
            Log::error('Tavily API Error (getCredits): ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            throw new \Exception('Failed to fetch API credits: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
