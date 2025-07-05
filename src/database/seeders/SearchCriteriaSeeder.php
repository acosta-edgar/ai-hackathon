<?php

namespace Database\Seeders;

use App\Models\SearchCriteria;
use Illuminate\Database\Seeder;

class SearchCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'Default Tech Jobs',
                'is_default' => true,
                'keywords' => ['software engineer', 'developer', 'programmer', 'full stack', 'frontend', 'backend'],
                'locations' => ['San Francisco', 'New York', 'Seattle', 'Austin', 'Boston', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 80000,
                'max_salary' => 200000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Software', 'SaaS', 'Fintech'],
                'skills_included' => ['JavaScript', 'Python', 'React', 'Node.js', 'AWS'],
                'skills_excluded' => ['PHP', 'WordPress'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Senior Developer Roles',
                'is_default' => false,
                'keywords' => ['senior', 'lead', 'principal', 'architect'],
                'locations' => ['San Francisco', 'New York', 'Seattle', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'senior',
                'min_salary' => 120000,
                'max_salary' => 250000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Software', 'SaaS'],
                'skills_included' => ['TypeScript', 'React', 'Node.js', 'AWS', 'Docker', 'Kubernetes'],
                'days_posted' => 14,
                'is_active' => true
            ],
            [
                'name' => 'Data Science Positions',
                'is_default' => false,
                'keywords' => ['data scientist', 'machine learning', 'ML engineer', 'data analyst'],
                'locations' => ['San Francisco', 'New York', 'Boston', 'Seattle', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 90000,
                'max_salary' => 180000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Finance', 'Healthcare', 'E-commerce'],
                'skills_included' => ['Python', 'R', 'SQL', 'TensorFlow', 'PyTorch', 'Pandas'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'UX/UI Design Jobs',
                'is_default' => false,
                'keywords' => ['UX designer', 'UI designer', 'product designer', 'interaction designer'],
                'locations' => ['San Francisco', 'New York', 'Austin', 'Seattle', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 70000,
                'max_salary' => 140000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Design', 'E-commerce', 'SaaS'],
                'skills_included' => ['Figma', 'Sketch', 'Adobe Creative Suite', 'User Research'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'DevOps & Infrastructure',
                'is_default' => false,
                'keywords' => ['devops', 'infrastructure', 'site reliability', 'platform engineer'],
                'locations' => ['San Francisco', 'New York', 'Seattle', 'Austin', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 100000,
                'max_salary' => 180000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Cloud Services', 'SaaS'],
                'skills_included' => ['AWS', 'Kubernetes', 'Docker', 'Terraform', 'Jenkins'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Product Management',
                'is_default' => false,
                'keywords' => ['product manager', 'product owner', 'product lead'],
                'locations' => ['San Francisco', 'New York', 'Boston', 'Seattle', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 100000,
                'max_salary' => 180000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'SaaS', 'Fintech', 'E-commerce'],
                'skills_included' => ['Product Strategy', 'Agile', 'User Research', 'Data Analysis'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Startup Opportunities',
                'is_default' => false,
                'keywords' => ['startup', 'early stage', 'seed', 'series A'],
                'locations' => ['San Francisco', 'New York', 'Austin', 'Boston', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 60000,
                'max_salary' => 150000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'SaaS', 'Fintech', 'Healthtech'],
                'companies' => ['startup', 'early-stage', 'seed-funded'],
                'days_posted' => 14,
                'is_active' => true
            ],
            [
                'name' => 'Remote Only',
                'is_default' => false,
                'keywords' => ['remote', 'work from home', 'distributed'],
                'locations' => ['Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'mid',
                'min_salary' => 70000,
                'max_salary' => 160000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'SaaS', 'Fintech', 'E-commerce'],
                'skills_included' => ['JavaScript', 'Python', 'React', 'Node.js', 'AWS'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Entry Level Tech',
                'is_default' => false,
                'keywords' => ['junior', 'entry level', 'graduate', 'new grad'],
                'locations' => ['San Francisco', 'New York', 'Seattle', 'Austin', 'Boston', 'Remote'],
                'post_type' => 'full-time',
                'experience_level' => 'entry',
                'min_salary' => 50000,
                'max_salary' => 90000,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Software', 'SaaS'],
                'skills_included' => ['JavaScript', 'Python', 'React', 'Node.js'],
                'days_posted' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Contract/Freelance',
                'is_default' => false,
                'keywords' => ['contract', 'freelance', 'consultant', 'temporary'],
                'locations' => ['San Francisco', 'New York', 'Seattle', 'Austin', 'Remote'],
                'post_type' => 'contract',
                'experience_level' => 'mid',
                'min_salary' => 50,
                'max_salary' => 150,
                'salary_currency' => 'USD',
                'is_remote' => true,
                'industries' => ['Technology', 'Software', 'SaaS'],
                'skills_included' => ['JavaScript', 'Python', 'React', 'Node.js', 'AWS'],
                'days_posted' => 30,
                'is_active' => true
            ]
        ];

        foreach ($criteria as $criterion) {
            SearchCriteria::create($criterion);
        }
    }
} 