<?php

namespace App\Services;

use App\Models\Post;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;

class PostMatchingService
{
    /**
     * The Gemini API service instance.
     *
     * @var \App\Services\GeminiApiService
     */
    protected $geminiService;

    /**
     * Create a new post matching service instance.
     *
     * @param  \App\Services\GeminiApiService  $geminiService
     * @return void
     */
    public function __construct(GeminiApiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Match posts to a user profile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  array  $posts
     * @param  array  $searchCriteria
     * @return array
     */
    public function matchPosts(UserProfile $userProfile, array $posts, array $searchCriteria = []): array
    {
        $matchedPosts = [];

        foreach ($posts as $post) {
            try {
                $matchResult = $this->matchPost($userProfile, $post, $searchCriteria);
                $matchedPosts[] = [
                    'post' => $post,
                    'match_result' => $matchResult,
                ];
            } catch (\Exception $e) {
                Log::error('Error matching post: ' . $e->getMessage(), [
                    'post_id' => $post->id,
                    'user_profile_id' => $userProfile->id,
                    'exception' => $e,
                ]);

                // Skip this post if there was an error
                continue;
            }
        }

        // Sort posts by match score (descending)
        usort($matchedPosts, function ($a, $b) {
            return $b['match_result']['overall_score'] <=> $a['match_result']['overall_score'];
        });

        return $matchedPosts;
    }

    /**
     * Match a single post to a user profile.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Post  $post
     * @param  array  $searchCriteria
     * @return array
     */
    public function matchPost(UserProfile $userProfile, Post $post, array $searchCriteria = []): array
    {
        // Convert models to arrays for the Gemini API
        $postData = $this->formatPostData($post);
        $profileData = $this->formatUserProfileData($userProfile);

        // Get the match analysis from Gemini
        $matchResult = $this->geminiService->analyzePostMatch($postData, $profileData, $searchCriteria);

        // Add additional metadata
        $matchResult['last_analyzed_at'] = now()->toDateTimeString();
        $matchResult['post_id'] = $post->id;
        $matchResult['user_profile_id'] = $userProfile->id;

        return $matchResult;
    }

    /**
     * Format post data for the matching service.
     *
     * @param  \App\Models\Post  $post
     * @return array
     */
    protected function formatPostData(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'company_name' => $post->company_name,
            'location' => $post->location,
            'post_type' => $post->post_type,
            'salary_min' => $post->salary_min,
            'salary_max' => $post->salary_max,
            'salary_currency' => $post->salary_currency,
            'description' => $post->description,
            'requirements' => $post->requirements,
            'skills' => $post->skills ?? [],
            'experience_level' => $post->experience_level,
            'education_level' => $post->education_level,
            'is_remote' => (bool) $post->is_remote,
            'posted_at' => $post->posted_at?->toDateTimeString(),
            'expires_at' => $post->expires_at?->toDateTimeString(),
            'post_url' => $post->post_url,
            'apply_url' => $post->apply_url,
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
                    'title' => $exp->post_title,
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
                'post_types' => $userProfile->post_type_preferences,
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
     * Generate a cover letter for a post application.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Post  $post
     * @param  array  $options
     * @return array
     */
    public function generateCoverLetter(UserProfile $userProfile, Post $post, array $options = []): array
    {
        $postData = $this->formatPostData($post);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->generateCoverLetter($postData, $profileData, $options);
    }

    /**
     * Generate interview preparation questions for a post.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Post  $post
     * @param  array  $options
     * @return array
     */
    public function generateInterviewQuestions(UserProfile $userProfile, Post $post, array $options = []): array
    {
        $postData = $this->formatPostData($post);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->generateInterviewQuestions($postData, $profileData, $options);
    }

    /**
     * Get skills improvement suggestions based on post requirements.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @param  \App\Models\Post  $post
     * @return array
     */
    public function getSkillsImprovementSuggestions(UserProfile $userProfile, Post $post): array
    {
        $postData = $this->formatPostData($post);
        $profileData = $this->formatUserProfileData($userProfile);

        return $this->geminiService->getSkillsImprovementSuggestions($postData, $profileData);
    }

    /**
     * Filter posts based on basic criteria before AI matching.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $posts
     * @param  array  $criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function preFilterPosts($posts, array $criteria)
    {
        // Filter by location if specified
        if (!empty($criteria['location'])) {
            $location = strtolower($criteria['location']);
            $posts = $posts->filter(function ($post) use ($location) {
                return str_contains(strtolower($post->location), $location) || 
                       ($post->is_remote && $location === 'remote');
            });
        }

        // Filter by post type if specified
        if (!empty($criteria['post_type'])) {
            $postType = strtolower($criteria['post_type']);
            $posts = $posts->filter(function ($post) use ($postType) {
                return str_contains(strtolower($post->post_type), $postType);
            });
        }

        // Filter by experience level if specified
        if (!empty($criteria['experience_level'])) {
            $expLevel = strtolower($criteria['experience_level']);
            $posts = $posts->filter(function ($post) use ($expLevel) {
                return str_contains(strtolower($post->experience_level), $expLevel);
            });
        }

        // Filter by salary range if specified
        if (!empty($criteria['min_salary'])) {
            $minSalary = (float) $criteria['min_salary'];
            $posts = $posts->filter(function ($post) use ($minSalary) {
                return $post->salary_max >= $minSalary || $post->salary_min >= $minSalary;
            });
        }

        // Filter by remote preference if specified
        if (isset($criteria['is_remote'])) {
            $isRemote = (bool) $criteria['is_remote'];
            $posts = $posts->filter(function ($post) use ($isRemote) {
                return $post->is_remote === $isRemote;
            });
        }

        return $posts;
    }
}
