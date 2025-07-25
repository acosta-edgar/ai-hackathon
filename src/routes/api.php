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
//Route::middleware('auth:api')->group(function () {
    // User Profile Routes
    Route::apiResource('user-profiles', UserProfileController::class);

    // Post Board Routes
    Route::apiResource('post-boards', PostBoardController::class);

    // Search Criteria Routes
    Route::apiResource('search-criteria', SearchCriteriaController::class);

    // Post Routes
    Route::apiResource('posts', PostController::class);

    // Post Match Routes
    Route::apiResource('post-matches', PostMatchController::class);

    // Custom Post Match Actions
    Route::prefix('post-matches/{postMatch}')->group(function () {
        Route::post('/view', [PostMatchController::class, 'markAsViewed']);
        Route::post('/apply', [PostMatchController::class, 'markAsApplied']);
        Route::post('/reject', [PostMatchController::class, 'markAsRejected']);
        Route::post('/interested', [PostMatchController::class, 'markAsInterested']);
        Route::post('/not-interested', [PostMatchController::class, 'markAsNotInterested']);
    });

    // Custom Post Actions
    Route::prefix('posts')->group(function () {
        Route::get('/search', [PostController::class, 'search']);
        Route::post('/{post}/scrape', [PostController::class, 'scrapeDetails']);
    });

    // Scraper API Routes
    Route::prefix('scraper')->group(function () {
        // Post Search
        Route::post('/search', [ScraperController::class, 'searchPosts']);
        
        // Post Details
        Route::get('/posts/{postId}', [ScraperController::class, 'getPostDetails']);
        
        // Company Information
        Route::get('/company', [ScraperController::class, 'getCompanyInfo']);
        
        // Similar Posts
        Route::get('/posts/{postId}/similar', [ScraperController::class, 'getSimilarPosts']);
        
        // AI Enrichment
        Route::post('/enrich', [ScraperController::class, 'enrichPostWithAi']);
    });
//});

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
