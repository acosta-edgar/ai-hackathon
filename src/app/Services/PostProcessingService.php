<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostBoard;
use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PostProcessingService
{
    /**
     * The post scraper service instance.
     *
     * @var \App\Services\PostScraperService
     */
    protected $postScraper;

    /**
     * The post matching service instance.
     *
     * @var \App\Services\PostMatchingService
     */
    protected $postMatcher;

    /**
     * Create a new post processing service instance.
     *
     * @param  \App\Services\PostScraperService  $postScraper
     * @param  \App\Services\PostMatchingService  $postMatcher
     * @return void
     */
    public function __construct(PostScraperService $postScraper, PostMatchingService $postMatcher)
    {
        $this->postScraper = $postScraper;
        $this->postMatcher = $postMatcher;
    }

    /**
     * Process posts for all active users with search criteria.
     *
     * @param  int  $maxPostsPerUser
     * @return array
     */
    public function processAllUsersPosts(int $maxPostsPerUser = 20): array
    {
        $results = [];
        $activeUsers = $this->getActiveUsersWithSearchCriteria();

        foreach ($activeUsers as $user) {
            try {
                $result = $this->processUserPosts($user, $maxPostsPerUser);
                $results[$user->id] = $result;
            } catch (\Exception $e) {
                Log::error("Error processing posts for user {$user->id}: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);
                $results[$user->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'posts_processed' => 0,
                ];
            }
        }

        return $results;
    }

    /**
     * Process posts for a specific user.
     *
     * @param  \App\Models\User  $user
     * @param  int  $maxPosts
     * @return array
     */
    public function processUserPosts($user, int $maxPosts = 20): array
    {
        $profile = $user->profile;
        if (!$profile) {
            throw new \Exception('User does not have a profile');
        }

        $searchCriteria = $profile->searchCriteria()->active()->first();
        if (!$searchCriteria) {
            throw new \Exception('User does not have active search criteria');
        }

        // Scrape posts based on search criteria
        $scrapedPosts = $this->scrapePostsForCriteria($searchCriteria, $maxPosts);
        
        // Filter out posts already in the database
        $newPosts = $this->filterExistingPosts($scrapedPosts, $user->id);
        
        if (empty($newPosts)) {
            return [
                'success' => true,
                'message' => 'No new posts found',
                'posts_processed' => 0,
            ];
        }

        // Save new posts
        $savedPosts = [];
        foreach ($newPosts as $postData) {
            try {
                $post = $this->createPost($postData, $user->id, $searchCriteria);
                $savedPosts[] = $post;
            } catch (\Exception $e) {
                Log::error('Error saving post: ' . $e->getMessage(), [
                    'post_data' => $postData,
                    'exception' => $e,
                ]);
                continue;
            }
        }

        // Match posts to user profile
        $matchedPosts = $this->matchPostsToProfile($savedPosts, $profile);

        return [
            'success' => true,
            'posts_processed' => count($savedPosts),
            'posts_matched' => count($matchedPosts),
            'posts' => $savedPosts,
        ];
    }

    /**
     * Scrape posts based on search criteria.
     *
     * @param  \App\Models\SearchCriteria  $criteria
     * @param  int  $maxResults
     * @return array
     */
    protected function scrapePostsForCriteria(SearchCriteria $criteria, int $maxResults): array
    {
        $searchParams = [
            'keywords' => $criteria->keywords,
            'location' => $criteria->location,
            'post_type' => $criteria->post_type,
            'experience_level' => $criteria->experience_level,
            'is_remote' => $criteria->is_remote,
            'salary_min' => $criteria->salary_min,
            'salary_max' => $criteria->salary_max,
            'salary_currency' => $criteria->salary_currency,
        ];

        // If specific post boards are selected, scrape each one
        $postBoards = $criteria->postBoards()->active()->get();
        $allPosts = [];

        if ($postBoards->isNotEmpty()) {
            foreach ($postBoards as $postBoard) {
                try {
                    $posts = $this->postScraper
                        ->setPostBoard($postBoard)
                        ->scrapePosts($searchParams, $maxResults);
                    $allPosts = array_merge($allPosts, $posts);
                } catch (\Exception $e) {
                    Log::error("Error scraping post board {$postBoard->id}: " . $e->getMessage(), [
                        'post_board_id' => $postBoard->id,
                        'exception' => $e,
                    ]);
                    continue;
                }
            }
        } else {
            // Scrape from all available    post boards
            $allPosts = $this->postScraper->scrapePosts($searchParams, $maxResults);
        }

        return $allPosts;
    }

    /**
     * Filter out posts that already exist in the database.
     *
     * @param  array  $posts
     * @param  int  $userId
     * @return array
     */
    protected function filterExistingPosts(array $posts, int $userId): array
    {
        if (empty($posts)) {
            return [];
        }

        // Extract post URLs for checking
        $urls = array_column($posts, 'post_url');
        
        // Find existing post URLs for this user
        $existingUrls = Post::where('user_id', $userId)
            ->whereIn('post_url', $urls)
            ->pluck('post_url')
            ->toArray();
        
        // Filter out existing posts
        return array_filter($posts, function($post) use ($existingUrls) {
            return !in_array($post['post_url'], $existingUrls, true);
        });
    }

    /**
     * Create a new post from scraped data.
     *
     * @param  array  $postData
     * @param  int  $userId
     * @param  \App\Models\SearchCriteria  $criteria
     * @return \App\Models\Post
     */
    protected function createPost(array $postData, int $userId, SearchCriteria $criteria): Post
    {
        return DB::transaction(function () use ($postData, $userId, $criteria) {
            $post = new Post([
                'user_id' => $userId,
                'search_criteria_id' => $criteria->id,
                'post_board_id' => $postData['post_board_id'] ?? null,
                'title' => $postData['title'],
                'company_name' => $postData['company_name'],
                'location' => $postData['location'],
                'post_type' => $postData['post_type'] ?? null,
                'salary_min' => $postData['salary_min'] ?? null,
                'salary_max' => $postData['salary_max'] ?? null,
                'salary_currency' => $postData['salary_currency'] ?? 'USD',
                'description' => $postData['description'] ?? '',
                'requirements' => $postData['requirements'] ?? null,
                'skills' => $postData['skills'] ?? [],
                'experience_level' => $postData['experience_level'] ?? null,
                'education_level' => $postData['education_level'] ?? null,
                'is_remote' => $postData['is_remote'] ?? false,
                'post_url' => $postData['post_url'],
                'apply_url' => $postData['apply_url'] ?? $postData['post_url'],
                'posted_at' => $postData['posted_at'] ?? now(),
                'expires_at' => $postData['expires_at'] ?? now()->addDays(30),
                'source' => $postData['source'] ?? 'unknown',
                'status' => 'new',
                'raw_data' => $postData['raw_data'] ?? null,
            ]);

            $post->save();

            // If skills are provided, sync them
            if (!empty($postData['skills'])) {
                $this->syncPostSkills($post, $postData['skills']);
            }

            return $post;
        });
    }

    /**
     * Sync post skills with the database.
     *
     * @param  \App\Models\Post  $post
     * @param  array  $skills
     * @return void
     */
    protected function syncPostSkills(Post $post, array $skills): void
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
        
        // Sync post skills
        $post->skills()->sync($skillIds);
    }

    /**
     * Match posts to a user profile.
     *
     * @param  array  $posts
     * @param  \App\Models\UserProfile  $profile
     * @return array
     */
    protected function matchPostsToProfile(array $posts, UserProfile $profile): array
    {
        if (empty($posts)) {
            return [];
        }

        $matchedPosts = [];
        
        foreach ($posts as $post) {
            try {
                $matchResult = $this->postMatcher->matchPost($profile, $post);
                
                // Create or update post match
                $postMatch = $post->matches()->updateOrCreate(
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
                
                $matchedPosts[] = $postMatch;
                
                // Update post status
                if ($post->status === 'new') {
                    $post->status = 'matched';
                    $post->save();
                }
            } catch (\Exception $e) {
                Log::error("Error matching post {$post->id} to profile {$profile->id}: " . $e->getMessage(), [
                    'post_id' => $post->id,
                    'profile_id' => $profile->id,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return $matchedPosts;
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
     * Process a single post for a user.
     *
     * @param  int  $postId
     * @param  int  $userId
     * @return array
     */
    public function processSinglePost(int $postId, int $userId): array
    {
        $post = Post::findOrFail($postId);
        $profile = UserProfile::where('user_id', $userId)->firstOrFail();
        
        // Match the post to the profile
        $matchResult = $this->postMatcher->matchPost($profile, $post);
        
        // Create or update post match
        $postMatch = $post->matches()->updateOrCreate(
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
        
        // Update post status if it was new
        if ($post->status === 'new') {
            $post->status = 'matched';
            $post->save();
        }
        
        return [
            'success' => true,
            'post_match' => $postMatch,
        ];
    }

    /**
     * Refresh matches for a user's posts.
     *
     * @param  int  $userId
     * @param  array  $filters
     * @return array
     */
    public function refreshUserPostMatches(int $userId, array $filters = []): array
    {
        $profile = UserProfile::where('user_id', $userId)->firstOrFail();
        
        $query = Post::where('user_id', $userId);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['post_type'])) {
            $query->where('post_type', $filters['post_type']);
        }
        
        if (!empty($filters['is_remote'])) {
            $query->where('is_remote', (bool) $filters['is_remote']);
        }
        
        $posts = $query->get();
        $refreshed = [];
        
        foreach ($posts as $post) {
            try {
                $matchResult = $this->postMatcher->matchPost($profile, $post);
                
                $postMatch = $post->matches()->updateOrCreate(
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
                
                $refreshed[] = $postMatch;
            } catch (\Exception $e) {
                Log::error("Error refreshing post match {$post->id} for user {$userId}: " . $e->getMessage(), [
                    'post_id' => $post->id,
                    'user_id' => $userId,
                    'exception' => $e,
                ]);
                continue;
            }
        }
        
        return [
            'success' => true,
            'refreshed_count' => count($refreshed),
            'post_matches' => $refreshed,
        ];
    }
}
