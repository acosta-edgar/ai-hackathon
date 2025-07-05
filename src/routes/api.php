<?php

use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\PostBoardController;
use App\Http\Controllers\API\SearchCriteriaController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\PostMatchController;
use App\Http\Controllers\Api\ScraperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/scraper/health', [ScraperController::class, 'healthCheck']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    // User Profile Routes
    Route::apiResource('user-profiles', UserProfileController::class);

    // Job Board Routes
    Route::apiResource('job-boards', JobBoardController::class);

    // Search Criteria Routes
    Route::apiResource('search-criteria', SearchCriteriaController::class);

    // Job Routes
    Route::apiResource('jobs', JobController::class);

    // Job Match Routes
    Route::apiResource('job-matches', JobMatchController::class);

    // Custom Job Match Actions
    Route::prefix('job-matches/{jobMatch}')->group(function () {
        Route::post('/view', [JobMatchController::class, 'markAsViewed']);
        Route::post('/apply', [JobMatchController::class, 'markAsApplied']);
        Route::post('/reject', [JobMatchController::class, 'markAsRejected']);
        Route::post('/interested', [JobMatchController::class, 'markAsInterested']);
        Route::post('/not-interested', [JobMatchController::class, 'markAsNotInterested']);
    });

    // Custom Job Actions
    Route::prefix('jobs')->group(function () {
        Route::get('/search', [JobController::class, 'search']);
        Route::post('/{job}/scrape', [JobController::class, 'scrapeDetails']);
    });

    // Scraper API Routes
    Route::prefix('scraper')->group(function () {
        // Job Search
        Route::post('/search', [ScraperController::class, 'searchJobs']);
        
        // Job Details
        Route::get('/jobs/{jobId}', [ScraperController::class, 'getJobDetails']);
        
        // Company Information
        Route::get('/company', [ScraperController::class, 'getCompanyInfo']);
        
        // Similar Jobs
        Route::get('/jobs/{jobId}/similar', [ScraperController::class, 'getSimilarJobs']);
        
        // AI Enrichment
        Route::post('/enrich', [ScraperController::class, 'enrichJobWithAi']);
    });
});

// Post Matching
Route::prefix('matching')->group(function () {
    Route::post('/match-posts', [PostMatchController::class, 'matchPosts']);
    Route::get('/suggestions', [PostMatchController::class, 'getSuggestions']);
});

// AI Features
Route::prefix('ai')->group(function () {
    Route::post('/generate-cover-letter', [PostMatchController::class, 'generateCoverLetter']);
    Route::post('/customize-resume', [UserProfileController::class, 'customizeResume']);
    Route::post('/analyze-match', [PostMatchController::class, 'analyzeMatch']);
});
