<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobBoard;
use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JobProcessingService
{
    /**
     * The job scraper service instance.
     *
     * @var \App\Services\JobScraperService
     */
    protected $jobScraper;

    /**
     * The job matching service instance.
     *
     * @var \App\Services\JobMatchingService
     */
    protected $jobMatcher;

    /**
     * Create a new job processing service instance.
     *
     * @param  \App\Services\JobScraperService  $jobScraper
     * @param  \App\Services\JobMatchingService  $jobMatcher
     * @return void
     */
    public function __construct(JobScraperService $jobScraper, JobMatchingService $jobMatcher)
    {
        $this->jobScraper = $jobScraper;
        $this->jobMatcher = $jobMatcher;
    }

    /**
     * Process jobs for all active users with search criteria.
     *
     * @param  int  $maxJobsPerUser
     * @return array
     */
    public function processAllUsersJobs(int $maxJobsPerUser = 20): array
    {
        $results = [];
        $activeUsers = $this->getActiveUsersWithSearchCriteria();

        foreach ($activeUsers as $user) {
            try {
                $result = $this->processUserJobs($user, $maxJobsPerUser);
                $results[$user->id] = $result;
            } catch (\Exception $e) {
                Log::error("Error processing jobs for user {$user->id}: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);
                $results[$user->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'jobs_processed' => 0,
                ];
            }
        }

        return $results;
    }

    /**
     * Process jobs for a specific user.
     *
     * @param  \App\Models\User  $user
     * @param  int  $maxJobs
     * @return array
     */
    public function processUserJobs($user, int $maxJobs = 20): array
    {
        $profile = $user->profile;
        if (!$profile) {
            throw new \Exception('User does not have a profile');
        }

        $searchCriteria = $profile->searchCriteria()->active()->first();
        if (!$searchCriteria) {
            throw new \Exception('User does not have active search criteria');
        }

        // Scrape jobs based on search criteria
        $scrapedJobs = $this->scrapeJobsForCriteria($searchCriteria, $maxJobs);
        
        // Filter out jobs already in the database
        $newJobs = $this->filterExistingJobs($scrapedJobs, $user->id);
        
        if (empty($newJobs)) {
            return [
                'success' => true,
                'message' => 'No new jobs found',
                'jobs_processed' => 0,
            ];
        }

        // Save new jobs
        $savedJobs = [];
        foreach ($newJobs as $jobData) {
            try {
                $job = $this->createJob($jobData, $user->id, $searchCriteria);
                $savedJobs[] = $job;
            } catch (\Exception $e) {
                Log::error('Error saving job: ' . $e->getMessage(), [
                    'job_data' => $jobData,
                    'exception' => $e,
                ]);
                continue;
            }
        }

        // Match jobs to user profile
        $matchedJobs = $this->matchJobsToProfile($savedJobs, $profile);

        return [
            'success' => true,
            'jobs_processed' => count($savedJobs),
            'jobs_matched' => count($matchedJobs),
            'jobs' => $savedJobs,
        ];
    }

    /**
     * Scrape jobs based on search criteria.
     *
     * @param  \App\Models\SearchCriteria  $criteria
     * @param  int  $maxResults
     * @return array
     */
    protected function scrapeJobsForCriteria(SearchCriteria $criteria, int $maxResults): array
    {
        $searchParams = [
            'keywords' => $criteria->keywords,
            'location' => $criteria->location,
            'job_type' => $criteria->job_type,
            'experience_level' => $criteria->experience_level,
            'is_remote' => $criteria->is_remote,
            'salary_min' => $criteria->salary_min,
            'salary_max' => $criteria->salary_max,
            'salary_currency' => $criteria->salary_currency,
        ];

        // If specific job boards are selected, scrape each one
        $jobBoards = $criteria->jobBoards()->active()->get();
        $allJobs = [];

        if ($jobBoards->isNotEmpty()) {
            foreach ($jobBoards as $jobBoard) {
                try {
                    $jobs = $this->jobScraper
                        ->setJobBoard($jobBoard)
                        ->scrapeJobs($searchParams, $maxResults);
                    $allJobs = array_merge($allJobs, $jobs);
                } catch (\Exception $e) {
                    Log::error("Error scraping job board {$jobBoard->id}: " . $e->getMessage(), [
                        'job_board_id' => $jobBoard->id,
                        'exception' => $e,
                    ]);
                    continue;
                }
            }
        } else {
            // Scrape from all available job boards
            $allJobs = $this->jobScraper->scrapeJobs($searchParams, $maxResults);
        }

        return $allJobs;
    }

    /**
     * Filter out jobs that already exist in the database.
     *
     * @param  array  $jobs
     * @param  int  $userId
     * @return array
     */
    protected function filterExistingJobs(array $jobs, int $userId): array
    {
        if (empty($jobs)) {
            return [];
        }

        // Extract job URLs for checking
        $urls = array_column($jobs, 'job_url');
        
        // Find existing job URLs for this user
        $existingUrls = Job::where('user_id', $userId)
            ->whereIn('job_url', $urls)
            ->pluck('job_url')
            ->toArray();
        
        // Filter out existing jobs
        return array_filter($jobs, function($job) use ($existingUrls) {
            return !in_array($job['job_url'], $existingUrls, true);
        });
    }

    /**
     * Create a new job from scraped data.
     *
     * @param  array  $jobData
     * @param  int  $userId
     * @param  \App\Models\SearchCriteria  $criteria
     * @return \App\Models\Job
     */
    protected function createJob(array $jobData, int $userId, SearchCriteria $criteria): Job
    {
        return DB::transaction(function () use ($jobData, $userId, $criteria) {
            $job = new Job([
                'user_id' => $userId,
                'search_criteria_id' => $criteria->id,
                'job_board_id' => $jobData['job_board_id'] ?? null,
                'title' => $jobData['title'],
                'company_name' => $jobData['company_name'],
                'location' => $jobData['location'],
                'job_type' => $jobData['job_type'] ?? null,
                'salary_min' => $jobData['salary_min'] ?? null,
                'salary_max' => $jobData['salary_max'] ?? null,
                'salary_currency' => $jobData['salary_currency'] ?? 'USD',
                'description' => $jobData['description'] ?? '',
                'requirements' => $jobData['requirements'] ?? null,
                'skills' => $jobData['skills'] ?? [],
                'experience_level' => $jobData['experience_level'] ?? null,
                'education_level' => $jobData['education_level'] ?? null,
                'is_remote' => $jobData['is_remote'] ?? false,
                'job_url' => $jobData['job_url'],
                'apply_url' => $jobData['apply_url'] ?? $jobData['job_url'],
                'posted_at' => $jobData['posted_at'] ?? now(),
                'expires_at' => $jobData['expires_at'] ?? now()->addDays(30),
                'source' => $jobData['source'] ?? 'unknown',
                'status' => 'new',
                'raw_data' => $jobData['raw_data'] ?? null,
            ]);

            $job->save();

            // If skills are provided, sync them
            if (!empty($jobData['skills'])) {
                $this->syncJobSkills($job, $jobData['skills']);
            }

            return $job;
        });
    }

    /**
     * Sync job skills with the database.
     *
     * @param  \App\Models\Job  $job
     * @param  array  $skills
     * @return void
     */
    protected function syncJobSkills(Job $job, array $skills): void
    {
        // Normalize skill names
        $skills = array_map('trim', $skills);
        $skills = array_map('strtolower', $skills);
        $skills = array_unique($skills);
        
        // Find or create skills
        $skillIds = [];
        foreach ($skills as $skillName) {
            $skill = \App\Models\Skill::firstOrCreate(
                ['name' => $skillName],
                ['slug' => \Illuminate\Support\Str::slug($skillName)]
            );
            $skillIds[] = $skill->id;
        }
        
        // Sync job skills
        $job->skills()->sync($skillIds);
    }

    /**
     * Match jobs to a user profile.
     *
     * @param  array  $jobs
     * @param  \App\Models\UserProfile  $profile
     * @return array
     */
    protected function matchJobsToProfile(array $jobs, UserProfile $profile): array
    {
        if (empty($jobs)) {
            return [];
        }

        $matchedJobs = [];
        
        foreach ($jobs as $job) {
            try {
                $matchResult = $this->jobMatcher->matchJob($profile, $job);
                
                // Create or update job match
                $jobMatch = $job->matches()->updateOrCreate(
                    ['user_profile_id' => $profile->id],
                    [
                        'match_score' => $matchResult['overall_score'],
                        'strengths' => $matchResult['strengths'] ?? [],
                        'weaknesses' => $matchResult['weaknesses'] ?? [],
                        'status' => 'new',
                        'last_matched_at' => now(),
                        'metadata' => $matchResult,
                    ]
                );
                
                $matchedJobs[] = $jobMatch;
                
                // Update job status
                if ($job->status === 'new') {
                    $job->status = 'matched';
                    $job->save();
                }
            } catch (\Exception $e) {
                Log::error("Error matching job {$job->id} to profile {$profile->id}: " . $e->getMessage(), [
                    'job_id' => $job->id,
                    'profile_id' => $profile->id,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return $matchedJobs;
    }

    /**
     * Get active users with search criteria.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActiveUsersWithSearchCriteria()
    {
        return \App\Models\User::whereHas('profile', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('profile.searchCriteria', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['profile', 'profile.searchCriteria'])
            ->get();
    }

    /**
     * Process a single job for a user.
     *
     * @param  int  $jobId
     * @param  int  $userId
     * @return array
     */
    public function processSingleJob(int $jobId, int $userId): array
    {
        $job = Job::findOrFail($jobId);
        $profile = UserProfile::where('user_id', $userId)->firstOrFail();
        
        // Match the job to the profile
        $matchResult = $this->jobMatcher->matchJob($profile, $job);
        
        // Create or update job match
        $jobMatch = $job->matches()->updateOrCreate(
            ['user_profile_id' => $profile->id],
            [
                'match_score' => $matchResult['overall_score'],
                'strengths' => $matchResult['strengths'] ?? [],
                'weaknesses' => $matchResult['weaknesses'] ?? [],
                'status' => 'new',
                'last_matched_at' => now(),
                'metadata' => $matchResult,
            ]
        );
        
        // Update job status if it was new
        if ($job->status === 'new') {
            $job->status = 'matched';
            $job->save();
        }
        
        return [
            'success' => true,
            'job_match' => $jobMatch,
        ];
    }

    /**
     * Refresh matches for a user's jobs.
     *
     * @param  int  $userId
     * @param  array  $filters
     * @return array
     */
    public function refreshUserJobMatches(int $userId, array $filters = []): array
    {
        $profile = UserProfile::where('user_id', $userId)->firstOrFail();
        
        $query = Job::where('user_id', $userId);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['job_type'])) {
            $query->where('job_type', $filters['job_type']);
        }
        
        if (!empty($filters['is_remote'])) {
            $query->where('is_remote', (bool) $filters['is_remote']);
        }
        
        $jobs = $query->get();
        $refreshed = [];
        
        foreach ($jobs as $job) {
            try {
                $matchResult = $this->jobMatcher->matchJob($profile, $job);
                
                $jobMatch = $job->matches()->updateOrCreate(
                    ['user_profile_id' => $profile->id],
                    [
                        'match_score' => $matchResult['overall_score'],
                        'strengths' => $matchResult['strengths'] ?? [],
                        'weaknesses' => $matchResult['weaknesses'] ?? [],
                        'status' => 'refreshed',
                        'last_matched_at' => now(),
                        'metadata' => $matchResult,
                    ]
                );
                
                $refreshed[] = $jobMatch;
            } catch (\Exception $e) {
                Log::error("Error refreshing job match {$job->id} for user {$userId}: " . $e->getMessage(), [
                    'job_id' => $job->id,
                    'user_id' => $userId,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return [
            'success' => true,
            'refreshed_count' => count($refreshed),
            'job_matches' => $refreshed,
        ];
    }
}
