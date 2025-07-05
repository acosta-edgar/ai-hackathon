<?php

namespace Database\Seeders;

use App\Models\PostBoard;
use Illuminate\Database\Seeder;

class PostBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boards = [
            [
                'name' => 'LinkedIn Jobs',
                'url' => 'https://www.linkedin.com/jobs/',
                'type' => 'general',
                'description' => 'Professional networking platform with comprehensive job listings across all industries.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'date_posted' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 6,
                'last_searched_at' => now()->subHours(2)
            ],
            [
                'name' => 'Indeed',
                'url' => 'https://www.indeed.com/',
                'type' => 'general',
                'description' => 'One of the largest job search engines with millions of job listings worldwide.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'salary' => true,
                    'date_posted' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 4,
                'last_searched_at' => now()->subHours(1)
            ],
            [
                'name' => 'Glassdoor',
                'url' => 'https://www.glassdoor.com/Jobs/',
                'type' => 'general',
                'description' => 'Job search platform with company reviews, salary information, and job listings.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'salary' => true,
                    'company_rating' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 8,
                'last_searched_at' => now()->subHours(3)
            ],
            [
                'name' => 'Stack Overflow Jobs',
                'url' => 'https://stackoverflow.com/jobs',
                'type' => 'tech',
                'description' => 'Job board specifically for developers and technology professionals.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'technologies' => true,
                    'salary' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 12,
                'last_searched_at' => now()->subHours(5)
            ],
            [
                'name' => 'AngelList Talent',
                'url' => 'https://angel.co/jobs',
                'type' => 'startup',
                'description' => 'Job board focused on startup companies and early-stage opportunities.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'startup_stage' => true,
                    'equity' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 24,
                'last_searched_at' => now()->subHours(8)
            ],
            [
                'name' => 'WeWorkRemotely',
                'url' => 'https://weworkremotely.com/',
                'type' => 'remote',
                'description' => 'Job board dedicated to remote work opportunities across all industries.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'categories' => true,
                    'salary' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 6,
                'last_searched_at' => now()->subHours(2)
            ],
            [
                'name' => 'Dribbble Jobs',
                'url' => 'https://dribbble.com/jobs',
                'type' => 'design',
                'description' => 'Job board for designers, creatives, and design-related positions.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'design_disciplines' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 12,
                'last_searched_at' => now()->subHours(6)
            ],
            [
                'name' => 'Built In',
                'url' => 'https://builtin.com/jobs',
                'type' => 'tech',
                'description' => 'Job board focused on technology companies and startup ecosystems in major cities.',
                'requires_authentication' => false,
                'authentication_details' => null,
                'search_parameters' => [
                    'keywords' => true,
                    'location' => true,
                    'experience_level' => true,
                    'job_type' => true,
                    'remote' => true,
                    'company_size' => true,
                    'funding_stage' => true
                ],
                'is_active' => true,
                'search_frequency_hours' => 8,
                'last_searched_at' => now()->subHours(4)
            ]
        ];

        foreach ($boards as $board) {
            PostBoard::create($board);
        }
    }
} 