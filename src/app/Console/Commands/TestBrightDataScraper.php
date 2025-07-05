<?php

namespace App\Console\Commands;

use App\Services\Scraper\BrightDataScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestBrightDataScraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-brightdata 
                            {query : Search query (e.g., "software engineer")}
                            {--location= : Location filter (e.g., "New York, NY")}
                            {--country=us : Country code (e.g., "us")}
                            {--limit=5 : Number of results to fetch}
                            {--test-job-id= : Test with a specific job ID for details}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Bright Data Scraper service with a search query or job ID';

    /**
     * Execute the console command.
     */
    public function handle(BrightDataScraperService $scraper)
    {
        $jobId = $this->option('test-job-id');
        
        if ($jobId) {
            return $this->testJobDetails($scraper, $jobId);
        }
        
        return $this->testJobSearch($scraper);
    }
    
    /**
     * Test job search functionality.
     */
    protected function testJobSearch(BrightDataScraperService $scraper): int
    {
        $query = $this->argument('query');
        $location = $this->option('location');
        $country = $this->option('country');
        $limit = (int) $this->option('limit');
        
        $this->info("Testing Bright Data Scraper with the following parameters:");
        $this->line("- Query: {$query}");
        $this->line("- Location: " . ($location ?: 'Not specified'));
        $this->line("- Country: {$country}");
        $this->line("- Result limit: {$limit}");
        $this->line("");
        
        try {
            $this->info("Searching for jobs...");
            $startTime = microtime(true);
            
            $jobs = $scraper->searchJobs([
                'query' => $query,
                'location' => $location,
                'country' => $country,
                'limit' => $limit,
            ]);
            
            $elapsed = round(microtime(true) - $startTime, 2);
            
            if (empty($jobs)) {
                $this->warn("No jobs found for the given criteria.");
                return 0;
            }
            
            $this->info("\nFound " . count($jobs) . " jobs (took {$elapsed}s):\n");
            
            $headers = ['#', 'ID', 'Title', 'Company', 'Location', 'Posted Date', 'Source'];
            $rows = [];
            
            foreach ($jobs as $index => $job) {
                $rows[] = [
                    $index + 1,
                    substr($job['id'] ?? 'N/A', 0, 8) . '...',
                    $job['title'] ?? 'N/A',
                    $job['company']['name'] ?? 'N/A',
                    $job['location'] ?? 'N/A',
                    $job['posted_date'] ?? 'N/A',
                    $job['source'] ?? 'N/A',
                ];
            }
            
            $this->table($headers, $rows);
            
            // Ask if user wants to see details for a specific job
            if ($this->confirm('Would you like to see details for a specific job?', true)) {
                $jobIndex = $this->ask('Enter the job number to view details', 1) - 1;
                
                if (isset($jobs[$jobIndex])) {
                    $jobId = $jobs[$jobIndex]['id'];
                    $this->testJobDetails($scraper, $jobId);
                } else {
                    $this->error('Invalid job number.');
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Bright Data Scraper Test Failed', [
                'query' => $query,
                'location' => $location,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
    
    /**
     * Test job details functionality.
     */
    protected function testJobDetails(BrightDataScraperService $scraper, string $jobId): int
    {
        $this->info("\nFetching details for job ID: {$jobId}");
        
        try {
            $startTime = microtime(true);
            $jobDetails = $scraper->getJobDetails($jobId);
            $elapsed = round(microtime(true) - $startTime, 2);
            
            $this->info("\nJob Details (took {$elapsed}s):\n");
            $this->line("Title:       " . ($jobDetails['title'] ?? 'N/A'));
            $this->line("Company:     " . ($jobDetails['company']['name'] ?? 'N/A'));
            $this->line("Location:    " . ($jobDetails['location'] ?? 'N/A'));
            $this->line("Posted Date: " . ($jobDetails['posted_date'] ?? 'N/A'));
            $this->line("Source:      " . ($jobDetails['source'] ?? 'N/A'));
            $this->line("Apply URL:   " . ($jobDetails['apply_url'] ?? 'N/A'));
            
            if (!empty($jobDetails['salary'])) {
                $salary = $jobDetails['salary'];
                $salaryStr = '';
                
                if (isset($salary['min'], $salary['max'])) {
                    $salaryStr = "{$salary['min']} - {$salary['max']} {$salary['currency']}/{$salary['period']}";
                } elseif (isset($salary['amount'])) {
                    $salaryStr = "{$salary['amount']} {$salary['currency']}/{$salary['period']}";
                }
                
                if ($salaryStr) {
                    $this->line("Salary:      {$salaryStr}");
                }
            }
            
            $this->line("");
            $this->info("Job Description:");
            $this->line(wordwrap(strip_tags($jobDetails['description'] ?? 'No description available.'), 100));
            
            // Test AI enrichment if available
            if ($this->confirm('\nWould you like to enrich this job with AI analysis?', false)) {
                $this->testAiEnrichment($scraper, $jobDetails);
            }
            
            // Test similar jobs if available
            if ($this->confirm('\nWould you like to find similar jobs?', false)) {
                $this->testSimilarJobs($scraper, $jobId);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error fetching job details: " . $e->getMessage());
            Log::error('Bright Data Job Details Test Failed', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
    
    /**
     * Test AI enrichment functionality.
     */
    protected function testAiEnrichment(BrightDataScraperService $scraper, array $jobData): void
    {
        $this->info("\nEnriching job with AI analysis...");
        
        try {
            $startTime = microtime(true);
            $enrichedJob = $scraper->enrichWithAiAnalysis($jobData);
            $elapsed = round(microtime(true) - $startTime, 2);
            
            $this->info("\nAI Enrichment Results (took {$elapsed}s):\n");
            
            if (!empty($enrichedJob['ai_analysis'])) {
                $analysis = $enrichedJob['ai_analysis'];
                
                if (!empty($analysis['skills'])) {
                    $this->info("Key Skills:");
                    $this->line(implode(', ', $analysis['skills']));
                    $this->line("");
                }
                
                if (!empty($analysis['summary'])) {
                    $this->info("Job Summary:");
                    $this->line(wordwrap($analysis['summary'], 100));
                    $this->line("");
                }
                
                if (!empty($analysis['requirements'])) {
                    $this->info("Key Requirements:");
                    foreach ($analysis['requirements'] as $req) {
                        $this->line("- {$req}");
                    }
                    $this->line("");
                }
                
                if (!empty($analysis['responsibilities'])) {
                    $this->info("Key Responsibilities:");
                    foreach ($analysis['responsibilities'] as $resp) {
                        $this->line("- {$resp}");
                    }
                    $this->line("");
                }
            } else {
                $this->warn("No AI analysis data available.");
            }
            
        } catch (\Exception $e) {
            $this->error("AI Enrichment failed: " . $e->getMessage());
            Log::error('AI Enrichment Test Failed', [
                'job_title' => $jobData['title'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Test similar jobs functionality.
     */
    protected function testSimilarJobs(BrightDataScraperService $scraper, string $jobId, int $limit = 3): void
    {
        $this->info("\nFinding similar jobs...");
        
        try {
            $startTime = microtime(true);
            $similarJobs = $scraper->getSimilarJobs($jobId, $limit);
            $elapsed = round(microtime(true) - $startTime, 2);
            
            $this->info("\nFound " . count($similarJobs) . " similar jobs (took {$elapsed}s):\n");
            
            if (empty($similarJobs)) {
                $this->warn("No similar jobs found.");
                return;
            }
            
            $headers = ['#', 'ID', 'Title', 'Company', 'Location', 'Match %'];
            $rows = [];
            
            foreach ($similarJobs as $index => $job) {
                $rows[] = [
                    $index + 1,
                    substr($job['id'] ?? 'N/A', 0, 8) . '...',
                    $job['title'] ?? 'N/A',
                    $job['company']['name'] ?? 'N/A',
                    $job['location'] ?? 'N/A',
                    isset($job['match_score']) ? round($job['match_score'] * 100) . '%' : 'N/A',
                ];
            }
            
            $this->table($headers, $rows);
            
            // Ask if user wants to see details for a similar job
            if ($this->confirm('\nWould you like to see details for a similar job?', false)) {
                $jobIndex = $this->ask('Enter the job number to view details', 1) - 1;
                
                if (isset($similarJobs[$jobIndex])) {
                    $similarJobId = $similarJobs[$jobIndex]['id'];
                    $this->testJobDetails($scraper, $similarJobId);
                } else {
                    $this->error('Invalid job number.');
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error finding similar jobs: " . $e->getMessage());
            Log::error('Similar Jobs Test Failed', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
