<?php

use Illuminate\Support\Facades\Route;

// API routes are in api.php
Route::prefix('api')->group(function () {
    // API routes are handled by api.php
});

// Frontend routes - serve Vue.js SPA
Route::get('/{any?}', function () {
    return view('frontend');
})->where('any', '.*');
