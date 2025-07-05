<?php

use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\JobBoardController;
use App\Http\Controllers\API\SearchCriteriaController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\JobMatchController;
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

// Job Matching
Route::prefix('matching')->group(function () {
    Route::post('/match-jobs', [JobMatchController::class, 'matchJobs']);
    Route::get('/suggestions', [JobMatchController::class, 'getSuggestions']);
});

// AI Features
Route::prefix('ai')->group(function () {
    Route::post('/generate-cover-letter', [JobMatchController::class, 'generateCoverLetter']);
    Route::post('/customize-resume', [UserProfileController::class, 'customizeResume']);
    Route::post('/analyze-match', [JobMatchController::class, 'analyzeMatch']);
});
