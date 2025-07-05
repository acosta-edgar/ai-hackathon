<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Scraper\BrightDataScraperService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScraperController extends Controller
{
    /**
     * @var BrightDataScraperService
     */
    protected $brightDataScraper;

    /**
     * Create a new controller instance.
     *
     * @param BrightDataScraperService $brightDataScraper
     * @return void
     */
    public function __construct(BrightDataScraperService $brightDataScraper)
    {
        $this->brightDataScraper = $brightDataScraper;
        
        // Apply auth middleware to protected endpoints
        $this->middleware('auth:api')->except(['healthCheck']);
    }

    /**
     * Health check endpoint
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toDateTimeString(),
            'service' => 'JobCompass Scraper API',
            'version' => '1.0.0',
        ]);
    }

    /**
     * Search for jobs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchJobs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'location' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:50',
            'filters' => 'nullable|array',
            'filters.*' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $jobs = $this->brightDataScraper->searchJobs([
                'query' => $request->input('query'),
                'location' => $request->input('location'),
                'country' => $request->input('country', 'us'),
                'page' => $request->input('page', 1),
                'limit' => $request->input('limit', 10),
                'filters' => $request->input('filters', []),
            ]);

            return response()->json([
                'success' => true,
                'data' => $jobs,
                'meta' => [
                    'total' => count($jobs),
                    'page' => (int) $request->input('page', 1),
                    'limit' => (int) $request->input('limit', 10),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Job search failed', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search for jobs. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get job details
     *
     * @param string $jobId
     * @return JsonResponse
     */
    public function getJobDetails(string $jobId): JsonResponse
    {
        try {
            $jobDetails = $this->brightDataScraper->getJobDetails($jobId);
            
            return response()->json([
                'success' => true,
                'data' => $jobDetails,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch job details', [
                'error' => $e->getMessage(),
                'job_id' => $jobId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job details. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get company information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required_without:company_url|string|max:255',
            'company_url' => 'required_without:company_id|string|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $companyId = $request->input('company_id');
            $companyUrl = $request->input('company_url');
            
            $companyInfo = $this->brightDataScraper->getCompanyInfo(
                $companyId ?: $companyUrl
            );
            
            return response()->json([
                'success' => true,
                'data' => $companyInfo,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch company info', [
                'error' => $e->getMessage(),
                'company_id' => $request->input('company_id'),
                'company_url' => $request->input('company_url'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch company information. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get similar jobs
     *
     * @param string $jobId
     * @param Request $request
     * @return JsonResponse
     */
    public function getSimilarJobs(string $jobId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $similarJobs = $this->brightDataScraper->getSimilarJobs(
                $jobId,
                $request->input('limit', 5)
            );
            
            return response()->json([
                'success' => true,
                'data' => $similarJobs,
                'meta' => [
                    'total' => count($similarJobs),
                    'job_id' => $jobId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch similar jobs', [
                'error' => $e->getMessage(),
                'job_id' => $jobId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch similar jobs. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Enrich job data with AI analysis
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enrichJobWithAi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'job_data' => 'required|array',
            'job_data.title' => 'required|string',
            'job_data.description' => 'required|string',
            'job_data.company' => 'required|array',
            'job_data.company.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $enrichedJob = $this->brightDataScraper->enrichWithAiAnalysis(
                $request->input('job_data')
            );
            
            return response()->json([
                'success' => true,
                'data' => $enrichedJob,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to enrich job with AI', [
                'error' => $e->getMessage(),
                'job_title' => $request->input('job_data.title'),
            ]);

            // Return the original job data if enrichment fails
            return response()->json([
                'success' => true, // Still return success but with original data
                'data' => $request->input('job_data'),
                'message' => 'Job data could not be enriched with AI analysis. Using original data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }
}
