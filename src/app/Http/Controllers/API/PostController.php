<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\PostBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource with optional filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Post::query();
        
        // Apply filters
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->has('company')) {
            $query->where('company_name', 'like', '%' . $request->company . '%');
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        if ($request->has('is_remote')) {
            $query->where('is_remote', $request->boolean('is_remote'));
        }
        
        if ($request->has('post_type')) {
            $query->where('post_type', $request->post_type);
        }
        
        if ($request->has('min_salary')) {
            $query->where('salary_max', '>=', $request->min_salary);
        }
        
        if ($request->has('max_salary')) {
            $query->where('salary_min', '<=', $request->max_salary);
        }
        
        if ($request->has('skills')) {
            $skills = is_array($request->skills) ? $request->skills : [$request->skills];
            $query->whereJsonContains('skills', $skills);
        }
        
        // Sort results
        $sortField = $request->get('sort_by', 'posted_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);
        
        $posts = $query->with('postBoard')->paginate($request->get('per_page', 15));
        
        return $this->sendPaginatedResponse($posts, 'Posts retrieved successfully');
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
            'post_board_id' => 'required|exists:post_boards,id',
            'external_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url',
            'location' => 'required|string|max:255',
            'is_remote' => 'boolean',
            'post_type' => 'nullable|string|max:100',
            'experience_level' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_period' => 'nullable|string|max:20',
            'skills' => 'nullable|array',
            'categories' => 'nullable|array',
            'apply_url' => 'nullable|url',
            'post_url' => 'required|url',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:posted_at',
            'is_active' => 'boolean',
            'raw_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Check for duplicate external_id for the same post board
        $existingPost = Post::where('post_board_id', $input['post_board_id'])
            ->where('external_id', $input['external_id'])
            ->first();

        if ($existingPost) {
            return $this->sendError('Post with this external ID already exists for the specified post board', [], 409);
        }

        $post = Post::create($input);

        return $this->sendResponse($post, 'Post created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = Post::with('postBoard')->find($id);

        if (is_null($post)) {
            return $this->sendError('Post not found');
        }

        return $this->sendResponse($post, 'Post retrieved successfully');
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
        $post = Post::find($id);

        if (is_null($post)) {
            return $this->sendError('Post not found');
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'company_name' => 'sometimes|required|string|max:255',
            'company_website' => 'nullable|url',
            'location' => 'sometimes|required|string|max:255',
            'is_remote' => 'boolean',
            'post_type' => 'nullable|string|max:100',
            'experience_level' => 'nullable|string|max:100',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_period' => 'nullable|string|max:20',
            'skills' => 'nullable|array',
            'categories' => 'nullable|array',
            'apply_url' => 'nullable|url',
            'post_url' => 'sometimes|required|url',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:posted_at',
            'is_active' => 'boolean',
            'raw_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $post->update($input);

        return $this->sendResponse($post, 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (is_null($post)) {
            return $this->sendError('Post not found');
        }

        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully');
    }

    /**
     * Search for posts based on search criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
            'location' => 'nullable|string',
            'post_type' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'is_remote' => 'nullable|boolean',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $query = Post::query();

        // Apply keyword search
        if ($request->has('keywords')) {
            $keywords = $request->keywords;
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('company_name', 'like', "%{$keyword}%");
                }
            });
        }

        // Apply location filter
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Apply post type filter
        if ($request->has('post_type')) {
            $query->where('post_type', $request->post_type);
        }

        // Apply experience level filter
        if ($request->has('experience_level')) {
            $query->where('experience_level', $request->experience_level);
        }

        // Apply salary filters
        if ($request->has('min_salary')) {
            $query->where('salary_max', '>=', $request->min_salary);
        }

        if ($request->has('max_salary')) {
            $query->where('salary_min', '<=', $request->max_salary);
        }

        // Apply remote filter
        if ($request->has('is_remote')) {
            $query->where('is_remote', $request->boolean('is_remote'));
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $posts = $query->with('postBoard')
            ->orderBy('posted_at', 'desc')
            ->paginate($perPage);

        return $this->sendPaginatedResponse($posts, 'Posts retrieved successfully');
    }

    /**
     * Scrape post details from the original URL.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function scrapeDetails($id)
    {
        $post = Post::find($id);

        if (is_null($post)) {
            return $this->sendError('Post not found');
        }

        // TODO: Implement post details scraping logic using Tavily API
        // This is a placeholder for the actual implementation
        $scrapedData = [
            'post_id' => $post->id,
            'details_updated' => false,
            'message' => 'Post details scraping not implemented yet',
        ];

        return $this->sendResponse($scrapedData, 'Post details scraping completed');
    }
}
