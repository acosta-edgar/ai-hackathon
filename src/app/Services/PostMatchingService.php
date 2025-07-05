<?php

namespace App\Services;

use App\Models\Job;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;

class JobMatchingService
{
    /**
     * The Gemini API service instance.
     *
     * @var \App\Services\GeminiApiService
     */
    protected $geminiService;

    /**
     * Create a new job matching service instance.
     *
     * @param  \App\Services\GeminiApiService  $geminiService
     * @return void
     */
    public function __construct(GeminiApiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Match jobs to a user profile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  array  $jobs
     * @param  array  $searchCriteria
     * @return array
     */
    public function matchJobs(UserProfile $userProfile, array $jobs, array $searchCriteria = []): array
    {
        $matchedJobs = [];

        foreach ($jobs as $job) {
            try {
                $matchResult = $this->matchJob($userProfile, $job, $searchCriteria);
                $matchedJobs[] = [
                    'job' => $job,
                    'match_result' => $matchResult,
                ];
            } catch (\Exception $e) {
                Log::error('Error matching job: ' . $e->getMessage(), [
                    'job_id' => $job->id,
                    'user_profile_id' => $userProfile->id,
                    'exception' => $e,
                ]);

                // Skip this job if there was an error
                continue;
            }
        }

        // Sort jobs by match score (descending)
        usort($matchedJobs, function ($a, $b) {
            return $b['match_result']['overall_score'] <=> $a['match_result']['overall_score'];
        });

        return $matchedJobs;
    }

    /**
     * Match a single job to a user profile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Job  $job
     * @param  array  $searchCriteria
     * @return array
     */
    public function matchJob(UserProfile $userProfile, Job $job, array $searchCriteria = []): array
    {
        // Convert models to arrays for the Gemini API
        $jobData = $this->formatJobData($job);
        $profileData = $this->formatUserProfileData($userProfile);

        // Get the match analysis from Gemini
        $matchResult = $this->geminiService->analyzeJobMatch($jobData, $profileData, $searchCriteria);

        // Add additional metadata
        $matchResult['last_analyzed_at'] = now()->toDateTimeString();
        $matchResult['job_id'] = $job->id;
        $matchResult['user_profile_id'] = $userProfile->id;

        return $matchResult;
    }

    /**
     * Format job data for the matching service.
     *
     * @param  \App\Models\Job  $job
     * @return array
     */
    protected function formatJobData(Job $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'company_name' => $job->company_name,
            'location' => $job->location,
            'job_type' => $job->job_type,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'salary_currency' => $job->salary_currency,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills ?? [],
            'experience_level' => $job->experience_level,
            'education_level' => $job->education_level,
            'is_remote' => (bool) $job->is_remote,
            'posted_at' => $job->posted_at?->toDateTimeString(),
            'expires_at' => $job->expires_at?->toDateTimeString(),
            'job_url' => $job->job_url,
            'apply_url' => $job->apply_url,
        ];
    }

    /**
     * Format user profile data for the matching service.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return array
     */
    protected function formatUserProfileData(UserProfile $userProfile): array
    {
        return [
            'id' => $userProfile->id,
            'name' => $userProfile->full_name,
            'title' => $userProfile->headline,
            'summary' => $userProfile->summary,
            'experience' => $userProfile->experience->map(function ($exp) {
                return [
                    'title' => $exp->job_title,
                    'company' => $exp->company_name,
                    'location' => $exp->location,
                    'start_date' => $exp->start_date?->format('Y-m'),
                    'end_date' => $exp->end_date?->format('Y-m'),
                    'current' => $exp->is_current,
                    'description' => $exp->description,
                    'skills' => $exp->skills ?? [],
                ];
            })->toArray(),
            'education' => $userProfile->education->map(function ($edu) {
                return [
                    'degree' => $edu->degree,
                    'field_of_study' => $edu->field_of_study,
                    'institution' => $edu->institution_name,
                    'start_date' => $edu->start_date?->format('Y-m'),
                    'end_date' => $edu->end_date?->format('Y-m'),
                    'current' => $edu->is_current,
                    'description' => $edu->description,
                ];
            })->toArray(),
            'skills' => $userProfile->skills->pluck('name')->toArray(),
            'certifications' => $userProfile->certifications->pluck('name')->toArray(),
            'languages' => $userProfile->languages->map(function ($lang) {
                return [
                    'name' => $lang->name,
                    'proficiency' => $lang->proficiency_level,
                ];
            })->toArray(),
            'preferences' => [
                'job_types' => $userProfile->job_type_preferences,
                'locations' => $userProfile->location_preferences,
                'salary_expectations' => [
                    'min' => $userProfile->salary_expectation_min,
                    'max' => $userProfile->salary_expectation_max,
                    'currency' => $userProfile->salary_expectation_currency,
                ],
                'remote_preference' => $userProfile->remote_preference,
            ],
        ];
    }

    /**
     * Generate a cover letter for a job application.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Job  $job
     * @param  array  $options
     * @return array
     */
    public function generateCoverLetter(UserProfile $userProfile, Job $job, array $options = []): array
    {
        $jobData = $this->formatJobData($job);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->generateCoverLetter($jobData, $profileData, $options);
    }

    /**
     * Generate interview preparation questions for a job.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Job  $job
     * @param  array  $options
     * @return array
     */
    public function generateInterviewQuestions(UserProfile $userProfile, Job $job, array $options = []): array
    {
        $jobData = $this->formatJobData($job);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->generateInterviewQuestions($jobData, $profileData, $options);
    }

    /**
     * Get skills improvement suggestions based on job requirements.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Job  $job
     * @return array
     */
    public function getSkillsImprovementSuggestions(UserProfile $userProfile, Job $job): array
    {
        $jobData = $this->formatJobData($job);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->getSkillsImprovementSuggestions($jobData, $profileData);
    }

    /**
     * Filter jobs based on basic criteria before AI matching.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $jobs
     * @param  array  $criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function preFilterJobs($jobs, array $criteria)
    {
        // Filter by location if specified
        if (!empty($criteria['location'])) {
            $location = strtolower($criteria['location']);
            $jobs = $jobs->filter(function ($job) use ($location) {
                return str_contains(strtolower($job->location), $location) || 
                       ($job->is_remote && $location === 'remote');
            });
        }

        // Filter by job type if specified
        if (!empty($criteria['job_type'])) {
            $jobType = strtolower($criteria['job_type']);
            $jobs = $jobs->filter(function ($job) use ($jobType) {
                return str_contains(strtolower($job->job_type), $jobType);
            });
        }

        // Filter by experience level if specified
        if (!empty($criteria['experience_level'])) {
            $expLevel = strtolower($criteria['experience_level']);
            $jobs = $jobs->filter(function ($job) use ($expLevel) {
                return str_contains(strtolower($job->experience_level), $expLevel);
            });
        }

        // Filter by salary range if specified
        if (!empty($criteria['min_salary'])) {
            $minSalary = (float) $criteria['min_salary'];
            $jobs = $jobs->filter(function ($job) use ($minSalary) {
                return $job->salary_max >= $minSalary || $job->salary_min >= $minSalary;
            });
        }

        // Filter by remote preference if specified
        if (isset($criteria['is_remote'])) {
            $isRemote = (bool) $criteria['is_remote'];
            $jobs = $jobs->filter(function ($job) use ($isRemote) {
                return $job->is_remote === $isRemote;
            });
        }

        return $jobs;
    }
}
