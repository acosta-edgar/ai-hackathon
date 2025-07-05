<?php

namespace App\Http\Controllers\API;

use App\Models\JobBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $jobBoards = JobBoard::paginate(10);
        return $this->sendPaginatedResponse($jobBoards, 'Job boards retrieved successfully');
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

        $jobBoard = JobBoard::create($input);

        return $this->sendResponse($jobBoard, 'Job board created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $jobBoard = JobBoard::find($id);

        if (is_null($jobBoard)) {
            return $this->sendError('Job board not found');
        }

        return $this->sendResponse($jobBoard, 'Job board retrieved successfully');
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
        $jobBoard = JobBoard::find($id);

        if (is_null($jobBoard)) {
            return $this->sendError('Job board not found');
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

        $jobBoard->update($input);

        return $this->sendResponse($jobBoard, 'Job board updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $jobBoard = JobBoard::find($id);

        if (is_null($jobBoard)) {
            return $this->sendError('Job board not found');
        }

        // Check if there are jobs associated with this board
        if ($jobBoard->jobs()->exists()) {
            return $this->sendError('Cannot delete job board with associated jobs', [], 409);
        }

        $jobBoard->delete();

        return $this->sendResponse([], 'Job board deleted successfully');
    }

    /**
     * Scrape jobs from the job board.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function scrapeJobs($id)
    {
        $jobBoard = JobBoard::find($id);

        if (is_null($jobBoard)) {
            return $this->sendError('Job board not found');
        }

        // TODO: Implement job scraping logic using Tavily API
        // This is a placeholder for the actual implementation
        $scrapedJobs = [
            'job_board_id' => $jobBoard->id,
            'jobs_scraped' => 0,
            'jobs_added' => 0,
            'jobs_updated' => 0,
            'last_scraped_at' => now()->toDateTimeString(),
        ];

        // Update the last scraped timestamp
        $jobBoard->update(['last_searched_at' => now()]);

        return $this->sendResponse($scrapedJobs, 'Job scraping completed successfully');
    }
}
