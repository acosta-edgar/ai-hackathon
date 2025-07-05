<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\PostMatch;
use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PostMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'status' => 'nullable|in:new,viewed,applied,interview,offer,rejected,closed',
            'min_score' => 'nullable|integer|min:0|max:100',
            'max_score' => 'nullable|integer|min:0|max:100',
            'is_interested' => 'nullable|boolean',
            'is_not_interested' => 'nullable|boolean',
            'sort_by' => 'nullable|in:score,posted_at,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = PostMatch::with(['post', 'searchCriteria'])
            ->where('user_profile_id', $request->user_profile_id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('min_score')) {
            $query->where('overall_score', '>=', $request->min_score);
        }

        if ($request->has('max_score')) {
            $query->where('overall_score', '<=', $request->max_score);
        }

        if ($request->has('is_interested')) {
            $query->where('is_interested', $request->boolean('is_interested'));
        }

        if ($request->has('is_not_interested')) {
            $query->where('is_not_interested', $request->boolean('is_not_interested'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'overall_score');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Special handling for post fields
        if (in_array($sortBy, ['posted_at', 'title', 'company_name'])) {
            $query->join('posts', 'post_matches.post_id', '=', 'posts.id')
                ->orderBy("posts.{$sortBy}", $sortOrder)
                ->select('post_matches.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $postMatches = $query->paginate($request->get('per_page', 15));

        return $this->sendPaginatedResponse($postMatches, 'Post matches retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'post_id' => 'required|exists:posts,id',
            'search_criteria_id' => 'nullable|exists:search_criteria,id',
            'overall_score' => 'required|integer|min:0|max:100',
            'skills_score' => 'nullable|integer|min:0|max:100',
            'experience_score' => 'nullable|integer|min:0|max:100',
            'education_score' => 'nullable|integer|min:0|max:100',
            'company_fit_score' => 'nullable|integer|min:0|max:100',
            'strengths' => 'nullable|array',
            'weaknesses' => 'nullable|array',
            'missing_skills' => 'nullable|array',
            'matching_skills' => 'nullable|array',
            'match_summary' => 'nullable|string',
            'improvement_suggestions' => 'nullable|string',
            'application_advice' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Check if this post match already exists
        $existingMatch = PostMatch::where('user_profile_id', $input['user_profile_id'])
            ->where('post_id', $input['post_id'])
            ->first();

        if ($existingMatch) {
            return $this->sendError('Post match already exists', [], 409);
        }

        // Set default status
        $input['status'] = PostMatch::STATUS_NEW;
        $input['status_history'] = [[
            'status' => PostMatch::STATUS_NEW,
            'changed_at' => now()->toDateTimeString(),
        ]];

        $postMatch = PostMatch::create($input);

        return $this->sendResponse($postMatch, 'Post match created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $postMatch = PostMatch::with(['post', 'userProfile', 'searchCriteria'])->find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        return $this->sendResponse($postMatch, 'Post match retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'overall_score' => 'sometimes|integer|min:0|max:100',
            'skills_score' => 'nullable|integer|min:0|max:100',
            'experience_score' => 'nullable|integer|min:0|max:100',
            'education_score' => 'nullable|integer|min:0|max:100',
            'company_fit_score' => 'nullable|integer|min:0|max:100',
            'strengths' => 'nullable|array',
            'weaknesses' => 'nullable|array',
            'missing_skills' => 'nullable|array',
            'matching_skills' => 'nullable|array',
            'match_summary' => 'nullable|string',
            'improvement_suggestions' => 'nullable|string',
            'application_advice' => 'nullable|string',
            'user_notes' => 'nullable|string',
            'status' => 'sometimes|in:new,viewed,applied,interview,offer,rejected,closed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Update status history if status is being changed
        if (isset($input['status']) && $input['status'] !== $postMatch->status) {
            $statusHistory = $postMatch->status_history ?? [];
            $statusHistory[] = [
                'status' => $input['status'],
                'changed_at' => now()->toDateTimeString(),
            ];
            $input['status_history'] = $statusHistory;
        }

        $postMatch->update($input);

        return $this->sendResponse($postMatch, 'Post match updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->delete();

        return $this->sendResponse([], 'Post match deleted successfully');
    }

    /**
     * Mark a post match as viewed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsViewed($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->markAsViewed();

        return $this->sendResponse($postMatch, 'Post match marked as viewed');
    }

    /**
     * Mark a post match as applied.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsApplied($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->markAsApplied();

        return $this->sendResponse($postMatch, 'Post application recorded');
    }

    /**
     * Mark a post match as rejected.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRejected($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->markAsRejected();

        return $this->sendResponse($postMatch, 'Post match marked as rejected');
    }

    /**
     * Mark a post as interested.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsInterested($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->update([
            'is_interested' => true,
            'is_not_interested' => false,
        ]);

        return $this->sendResponse($postMatch, 'Post marked as interested');
    }

    /**
     * Mark a post as not interested.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsNotInterested($id)
    {
        $postMatch = PostMatch::find($id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        $postMatch->update([
            'is_interested' => false,
            'is_not_interested' => true,
        ]);

        return $this->sendResponse($postMatch, 'Post marked as not interested');
    }

    /**
     * Match posts for a user profile based on search criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchPosts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'search_criteria_id' => 'nullable|exists:search_criteria,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'min_score' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userProfile = UserProfile::find($request->user_profile_id);
        $searchCriteria = $request->has('search_criteria_id') 
            ? SearchCriteria::find($request->search_criteria_id)
            : SearchCriteria::where('user_profile_id', $request->user_profile_id)
                ->where('is_default', true)
                ->first();

        if (!$searchCriteria) {
            return $this->sendError('No search criteria found');
        }

        // TODO: Implement post matching algorithm
        // This is a placeholder for the actual implementation
        $matchedPosts = [];
        $message = 'Post matching not fully implemented yet';

        return $this->sendResponse([
            'user_profile_id' => $userProfile->id,
            'search_criteria_id' => $searchCriteria->id,
            'matched_posts_count' => count($matchedPosts),
            'matched_posts' => $matchedPosts,
        ], $message);
    }

    /**
     * Get post match suggestions for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $limit = $request->get('limit', 5);

        // Get top matches that the user hasn't seen or interacted with yet
        $suggestions = PostMatch::with(['post'])
            ->where('user_profile_id', $request->user_profile_id)
            ->where('is_interested', false)
            ->where('is_not_interested', false)
            ->whereNotIn('status', [PostMatch::STATUS_REJECTED, PostMatch::STATUS_CLOSED])
            ->orderBy('overall_score', 'desc')
            ->limit($limit)
            ->get();

        return $this->sendResponse($suggestions, 'Post suggestions retrieved successfully');
    }

    /**
     * Generate a cover letter for a post match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCoverLetter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_match_id' => 'required|exists:post_matches,id',
            'tone' => 'nullable|in:formal,enthusiastic,professional,friendly',
            'length' => 'nullable|in:short,medium,long',
            'highlight_skills' => 'nullable|boolean',
            'include_salary_expectations' => 'nullable|boolean',
            'custom_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postMatch = PostMatch::with(['post', 'userProfile'])->find($request->post_match_id);

        if (is_null($postMatch)) {
            return $this->sendError('Post match not found');
        }

        // TODO: Implement cover letter generation using Gemini API
        // This is a placeholder for the actual implementation
        $coverLetter = [
            'post_match_id' => $postMatch->id,
            'content' => 'Generated cover letter content will appear here.',
            'tone' => $request->get('tone', 'professional'),
            'length' => $request->get('length', 'medium'),
            'highlighted_skills' => $postMatch->matching_skills ?? [],
            'generated_at' => now()->toDateTimeString(),
        ];

        return $this->sendResponse($coverLetter, 'Cover letter generated successfully');
    }

    /**
     * Analyze match between a user profile and a post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeMatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'post_id' => 'required|exists:posts,id',
            'search_criteria_id' => 'nullable|exists:search_criteria,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userProfile = UserProfile::find($request->user_profile_id);
        $post = Post::find($request->post_id);
        $searchCriteria = $request->has('search_criteria_id')
            ? SearchCriteria::find($request->search_criteria_id)
            : null;

        // TODO: Implement AI-powered match analysis using Gemini API
        // This is a placeholder for the actual implementation
        $analysis = [
            'user_profile_id' => $userProfile->id,
            'post_id' => $post->id,
            'overall_score' => rand(60, 95), // Random score for demo
            'skills_match' => [
                'score' => rand(50, 100),
                'matching_skills' => array_slice($userProfile->skills ?? [], 0, 5) ?: ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'MySQL'],
                'missing_skills' => ['Redis', 'Docker', 'AWS'],
            ],
            'experience_match' => [
                'score' => rand(60, 100),
                'years_experience_match' => true,
                'industry_experience_match' => true,
            ],
            'education_match' => [
                'score' => rand(70, 100),
                'degree_required' => true,
                'degree_matched' => true,
            ],
            'company_culture_fit' => [
                'score' => rand(50, 90),
                'values_alignment' => 'High',
                'work_style_match' => 'Moderate',
            ],
            'strengths' => [
                'Strong technical skills match',
                'Relevant work experience',
                'Educational background aligns with requirements',
            ],
            'weaknesses' => [
                'Limited experience with some required technologies',
                'May need to relocate',
            ],
            'recommendations' => [
                'Highlight your experience with ' . implode(', ', array_slice($userProfile->skills ?? [], 0, 2)) . ' in your application',
                'Consider gaining experience with ' . implode(', ', ['Redis', 'Docker']),
                'Emphasize your experience in similar roles',
            ],
            'generated_at' => now()->toDateTimeString(),
        ];

        return $this->sendResponse($analysis, 'Match analysis completed successfully');
    }
}
