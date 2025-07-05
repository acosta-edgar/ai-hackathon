<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Models\JobMatch;
use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class JobMatchController extends Controller
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

        $query = JobMatch::with(['job', 'searchCriteria'])
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
        
        // Special handling for job fields
        if (in_array($sortBy, ['posted_at', 'title', 'company_name'])) {
            $query->join('jobs', 'job_matches.job_id', '=', 'jobs.id')
                ->orderBy("jobs.{$sortBy}", $sortOrder)
                ->select('job_matches.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $jobMatches = $query->paginate($request->get('per_page', 15));

        return $this->sendPaginatedResponse($jobMatches, 'Job matches retrieved successfully');
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
            'job_id' => 'required|exists:jobs,id',
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

        // Check if this job match already exists
        $existingMatch = JobMatch::where('user_profile_id', $input['user_profile_id'])
            ->where('job_id', $input['job_id'])
            ->first();

        if ($existingMatch) {
            return $this->sendError('Job match already exists', [], 409);
        }

        // Set default status
        $input['status'] = JobMatch::STATUS_NEW;
        $input['status_history'] = [[
            'status' => JobMatch::STATUS_NEW,
            'changed_at' => now()->toDateTimeString(),
        ]];

        $jobMatch = JobMatch::create($input);

        return $this->sendResponse($jobMatch, 'Job match created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $jobMatch = JobMatch::with(['job', 'userProfile', 'searchCriteria'])->find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        return $this->sendResponse($jobMatch, 'Job match retrieved successfully');
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
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
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
        if (isset($input['status']) && $input['status'] !== $jobMatch->status) {
            $statusHistory = $jobMatch->status_history ?? [];
            $statusHistory[] = [
                'status' => $input['status'],
                'changed_at' => now()->toDateTimeString(),
            ];
            $input['status_history'] = $statusHistory;
        }

        $jobMatch->update($input);

        return $this->sendResponse($jobMatch, 'Job match updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->delete();

        return $this->sendResponse([], 'Job match deleted successfully');
    }

    /**
     * Mark a job match as viewed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsViewed($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->markAsViewed();

        return $this->sendResponse($jobMatch, 'Job match marked as viewed');
    }

    /**
     * Mark a job match as applied.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsApplied($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->markAsApplied();

        return $this->sendResponse($jobMatch, 'Job application recorded');
    }

    /**
     * Mark a job match as rejected.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRejected($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->markAsRejected();

        return $this->sendResponse($jobMatch, 'Job match marked as rejected');
    }

    /**
     * Mark a job as interested.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsInterested($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->update([
            'is_interested' => true,
            'is_not_interested' => false,
        ]);

        return $this->sendResponse($jobMatch, 'Job marked as interested');
    }

    /**
     * Mark a job as not interested.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsNotInterested($id)
    {
        $jobMatch = JobMatch::find($id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        $jobMatch->update([
            'is_interested' => false,
            'is_not_interested' => true,
        ]);

        return $this->sendResponse($jobMatch, 'Job marked as not interested');
    }

    /**
     * Match jobs for a user profile based on search criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchJobs(Request $request)
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

        // TODO: Implement job matching algorithm
        // This is a placeholder for the actual implementation
        $matchedJobs = [];
        $message = 'Job matching not fully implemented yet';

        return $this->sendResponse([
            'user_profile_id' => $userProfile->id,
            'search_criteria_id' => $searchCriteria->id,
            'matched_jobs_count' => count($matchedJobs),
            'matched_jobs' => $matchedJobs,
        ], $message);
    }

    /**
     * Get job match suggestions for a user.
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
        $suggestions = JobMatch::with(['job'])
            ->where('user_profile_id', $request->user_profile_id)
            ->where('is_interested', false)
            ->where('is_not_interested', false)
            ->whereNotIn('status', [JobMatch::STATUS_REJECTED, JobMatch::STATUS_CLOSED])
            ->orderBy('overall_score', 'desc')
            ->limit($limit)
            ->get();

        return $this->sendResponse($suggestions, 'Job suggestions retrieved successfully');
    }

    /**
     * Generate a cover letter for a job match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCoverLetter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_match_id' => 'required|exists:job_matches,id',
            'tone' => 'nullable|in:formal,enthusiastic,professional,friendly',
            'length' => 'nullable|in:short,medium,long',
            'highlight_skills' => 'nullable|boolean',
            'include_salary_expectations' => 'nullable|boolean',
            'custom_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $jobMatch = JobMatch::with(['job', 'userProfile'])->find($request->job_match_id);

        if (is_null($jobMatch)) {
            return $this->sendError('Job match not found');
        }

        // TODO: Implement cover letter generation using Gemini API
        // This is a placeholder for the actual implementation
        $coverLetter = [
            'job_match_id' => $jobMatch->id,
            'content' => 'Generated cover letter content will appear here.',
            'tone' => $request->get('tone', 'professional'),
            'length' => $request->get('length', 'medium'),
            'highlighted_skills' => $jobMatch->matching_skills ?? [],
            'generated_at' => now()->toDateTimeString(),
        ];

        return $this->sendResponse($coverLetter, 'Cover letter generated successfully');
    }

    /**
     * Analyze match between a user profile and a job.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeMatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'job_id' => 'required|exists:jobs,id',
            'search_criteria_id' => 'nullable|exists:search_criteria,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $userProfile = UserProfile::find($request->user_profile_id);
        $job = Job::find($request->job_id);
        $searchCriteria = $request->has('search_criteria_id')
            ? SearchCriteria::find($request->search_criteria_id)
            : null;

        // TODO: Implement AI-powered match analysis using Gemini API
        // This is a placeholder for the actual implementation
        $analysis = [
            'user_profile_id' => $userProfile->id,
            'job_id' => $job->id,
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
