<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bright Data Web Scraper API
    |--------------------------------------------------------------------------
    |
    | Configuration for the Bright Data Web Scraper API used for scraping
    | job postings from various sources including LinkedIn.
    |
    */
    'bright_data' => [
        'api_url' => env('BRIGHT_DATA_API_URL', 'https://api.brightdata.com'),
        'username' => env('BRIGHT_DATA_USERNAME'),
        'password' => env('BRIGHT_DATA_PASSWORD'),
        'customer_id' => env('BRIGHT_DATA_CUSTOMER_ID'),
        'zone' => env('BRIGHT_DATA_ZONE', 'linkedin'),
        'timeout' => env('BRIGHT_DATA_TIMEOUT', 120), // seconds
        'retry_attempts' => env('BRIGHT_DATA_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('BRIGHT_DATA_RETRY_DELAY', 2), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Tavily AI Search API
    |--------------------------------------------------------------------------
    |
    | Configuration for the Tavily AI Search API used for web search
    | and information retrieval.
    |
    */
    'tavily' => [
        'api_key' => env('TAVILY_API_KEY'),
        'base_url' => env('TAVILY_BASE_URL', 'https://api.tavily.com'),
        'timeout' => env('TAVILY_TIMEOUT', 60), // seconds
        'max_results' => env('TAVILY_MAX_RESULTS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini API
    |--------------------------------------------------------------------------
    |
    | Configuration for the Google Gemini API used for AI-powered
    | job matching and content generation.
    |
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'project_id' => env('GEMINI_PROJECT_ID'),
        'location' => env('GEMINI_LOCATION', 'us-central1'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-pro'),
        'temperature' => env('GEMINI_TEMPERATURE', 0.2),
        'max_output_tokens' => env('GEMINI_MAX_OUTPUT_TOKENS', 2048),
        'top_p' => env('GEMINI_TOP_P', 0.8),
        'top_k' => env('GEMINI_TOP_K', 40),
    ],
];
