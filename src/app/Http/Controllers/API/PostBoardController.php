<?php

namespace App\Http\Controllers\API;

use App\Models\PostBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $postBoards = PostBoard::paginate(10);
        return $this->sendPaginatedResponse($postBoards, 'Post boards retrieved successfully');
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
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'requires_authentication' => 'boolean',
            'authentication_details' => 'nullable|array',
            'search_parameters' => 'nullable|array',
            'search_frequency_hours' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postBoard = PostBoard::create($input);

        return $this->sendResponse($postBoard, 'Post board created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $postBoard = PostBoard::find($id);

        if (is_null($postBoard)) {
            return $this->sendError('Post board not found');
        }

        return $this->sendResponse($postBoard, 'Post board retrieved successfully');
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
        $postBoard = PostBoard::find($id);

        if (is_null($postBoard)) {
            return $this->sendError('Post board not found');
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'requires_authentication' => 'boolean',
            'authentication_details' => 'nullable|array',
            'search_parameters' => 'nullable|array',
            'search_frequency_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $postBoard->update($input);

        return $this->sendResponse($postBoard, 'Post board updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $postBoard = PostBoard::find($id);

        if (is_null($postBoard)) {
            return $this->sendError('Post board not found');
        }

        // Check if there are posts associated with this board
        if ($postBoard->posts()->exists()) {
            return $this->sendError('Cannot delete post board with associated posts', [], 409);
        }

        $postBoard->delete();

        return $this->sendResponse([], 'Post board deleted successfully');
    }

    /**
     * Scrape posts from the post board.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function scrapePosts($id)
    {
        $postBoard = PostBoard::find($id);

        if (is_null($postBoard)) {
            return $this->sendError('Post board not found');
        }

        // TODO: Implement post scraping logic using Tavily API
        // This is a placeholder for the actual implementation
        $scrapedPosts = [
            'post_board_id' => $postBoard->id,
            'posts_scraped' => 0,
            'posts_added' => 0,
            'posts_updated' => 0,
            'last_scraped_at' => now()->toDateTimeString(),
        ];

        // Update the last scraped timestamp
        $postBoard->update(['last_searched_at' => now()]);

        return $this->sendResponse($scrapedPosts, 'Post scraping completed successfully');
    }
}
