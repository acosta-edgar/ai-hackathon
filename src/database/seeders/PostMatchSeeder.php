<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostMatch;
use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class PostMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userProfiles = UserProfile::all();
        $posts = Post::all();
        $searchCriteria = SearchCriteria::all();

        // Create matches for Sarah Johnson (Senior Full Stack Developer)
        $sarah = $userProfiles->where('name', 'Sarah Johnson')->first();
        $sarahMatches = [
            [
                'post_id' => $posts->where('title', 'Senior Full Stack Developer')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'Senior Developer Roles')->first()->id,
                'overall_score' => 92,
                'skills_score' => 95,
                'experience_score' => 90,
                'education_score' => 85,
                'company_fit_score' => 88,
                'strengths' => [
                    'Strong match in required skills (React, Node.js, AWS)',
                    'Experience level aligns perfectly with senior role',
                    'Previous experience in similar tech stack',
                    'Good educational background in Computer Science'
                ],
                'weaknesses' => [
                    'Could benefit from more TypeScript experience',
                    'Limited experience with Docker in current role'
                ],
                'missing_skills' => ['TypeScript'],
                'matching_skills' => ['JavaScript', 'React', 'Node.js', 'AWS', 'PostgreSQL'],
                'match_summary' => 'Excellent match for this senior full-stack position. Sarah has strong experience with the required technologies and a proven track record in similar roles. Her background in both frontend and backend development makes her an ideal candidate.',
                'improvement_suggestions' => [
                    'Consider gaining more TypeScript experience',
                    'Practice with Docker containerization',
                    'Update portfolio with recent React/Node.js projects'
                ],
                'application_advice' => 'Emphasize your experience with React and Node.js. Highlight your mentoring experience and CI/CD pipeline implementation. Mention your passion for clean, maintainable code.',
                'is_interested' => true,
                'status' => 'viewed',
                'viewed_at' => now()->subHours(2)
            ],
            [
                'post_id' => $posts->where('title', 'Full Stack Developer (Vue.js)')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'Default Tech Jobs')->first()->id,
                'overall_score' => 78,
                'skills_score' => 75,
                'experience_score' => 85,
                'education_score' => 85,
                'company_fit_score' => 80,
                'strengths' => [
                    'Strong full-stack development experience',
                    'Good educational background',
                    'Experience with modern web technologies'
                ],
                'weaknesses' => [
                    'Limited Vue.js experience (more React focused)',
                    'Salary expectations may be higher than offered range'
                ],
                'missing_skills' => ['Vue.js'],
                'matching_skills' => ['TypeScript', 'Node.js', 'PostgreSQL', 'AWS', 'Docker'],
                'match_summary' => 'Good match for this full-stack position, though Sarah would need to learn Vue.js. Her strong backend experience and TypeScript knowledge are valuable assets.',
                'improvement_suggestions' => [
                    'Learn Vue.js fundamentals',
                    'Build a small Vue.js project for portfolio',
                    'Consider if salary range meets expectations'
                ],
                'application_advice' => 'Highlight your TypeScript and Node.js experience. Mention your willingness to learn Vue.js and your track record of quickly adapting to new technologies.',
                'is_interested' => null,
                'status' => 'new'
            ]
        ];

        foreach ($sarahMatches as $match) {
            PostMatch::create(array_merge($match, ['user_profile_id' => $sarah->id]));
        }

        // Create matches for Michael Chen (Data Scientist)
        $michael = $userProfiles->where('name', 'Michael Chen')->first();
        $michaelMatches = [
            [
                'post_id' => $posts->where('title', 'Data Scientist')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'Data Science Positions')->first()->id,
                'overall_score' => 95,
                'skills_score' => 98,
                'experience_score' => 90,
                'education_score' => 95,
                'company_fit_score' => 92,
                'strengths' => [
                    'Perfect skill match (Python, TensorFlow, PyTorch, Pandas)',
                    'Strong educational background in Data Science',
                    'Relevant work experience in predictive analytics',
                    'Certifications in cloud and ML technologies'
                ],
                'weaknesses' => [
                    'Could benefit from more big data experience',
                    'Limited experience with Apache Spark'
                ],
                'missing_skills' => ['Apache Spark'],
                'matching_skills' => ['Python', 'R', 'SQL', 'TensorFlow', 'PyTorch', 'Pandas'],
                'match_summary' => 'Exceptional match for this data scientist position. Michael has all the required skills and relevant experience in machine learning and predictive analytics. His educational background and certifications make him an ideal candidate.',
                'improvement_suggestions' => [
                    'Gain experience with Apache Spark',
                    'Practice with larger datasets',
                    'Consider contributing to open-source ML projects'
                ],
                'application_advice' => 'Emphasize your experience with TensorFlow and PyTorch. Highlight your success in reducing customer churn by 25%. Mention your cloud certifications and experience with big data technologies.',
                'is_interested' => true,
                'status' => 'applied',
                'viewed_at' => now()->subDays(1),
                'applied_at' => now()->subHours(6)
            ],
            [
                'post_id' => $posts->where('title', 'Machine Learning Engineer')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'Data Science Positions')->first()->id,
                'overall_score' => 88,
                'skills_score' => 90,
                'experience_score' => 85,
                'education_score' => 95,
                'company_fit_score' => 90,
                'strengths' => [
                    'Strong ML skills (TensorFlow, PyTorch)',
                    'Excellent educational background',
                    'Experience with Python and cloud platforms'
                ],
                'weaknesses' => [
                    'Limited MLOps experience',
                    'Could benefit from more Kubernetes knowledge'
                ],
                'missing_skills' => ['MLOps', 'Kubernetes'],
                'matching_skills' => ['Python', 'TensorFlow', 'PyTorch', 'Docker', 'AWS'],
                'match_summary' => 'Very good match for this ML engineer position. Michael has strong machine learning skills and cloud experience, though he would need to learn MLOps practices.',
                'improvement_suggestions' => [
                    'Learn MLOps fundamentals',
                    'Gain experience with Kubernetes',
                    'Practice deploying ML models to production'
                ],
                'application_advice' => 'Highlight your strong ML background and experience with TensorFlow/PyTorch. Emphasize your willingness to learn MLOps and your track record of building predictive models.',
                'is_interested' => true,
                'status' => 'viewed',
                'viewed_at' => now()->subHours(4)
            ]
        ];

        foreach ($michaelMatches as $match) {
            PostMatch::create(array_merge($match, ['user_profile_id' => $michael->id]));
        }

        // Create matches for Emily Rodriguez (UX/UI Designer)
        $emily = $userProfiles->where('name', 'Emily Rodriguez')->first();
        $emilyMatches = [
            [
                'post_id' => $posts->where('title', 'UX/UI Designer')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'UX/UI Design Jobs')->first()->id,
                'overall_score' => 90,
                'skills_score' => 92,
                'experience_score' => 88,
                'education_score' => 85,
                'company_fit_score' => 90,
                'strengths' => [
                    'Perfect skill match (Figma, User Research, Prototyping)',
                    'Relevant work experience in UX design',
                    'Strong educational background in design',
                    'Experience with design systems'
                ],
                'weaknesses' => [
                    'Could benefit from more user testing experience',
                    'Limited experience with Sketch'
                ],
                'missing_skills' => ['Sketch'],
                'matching_skills' => ['Figma', 'Adobe Creative Suite', 'User Research', 'Prototyping'],
                'match_summary' => 'Excellent match for this UX/UI designer position. Emily has all the required skills and relevant experience in user research and design systems.',
                'improvement_suggestions' => [
                    'Gain more experience with Sketch',
                    'Practice user testing methodologies',
                    'Build portfolio with recent UX projects'
                ],
                'application_advice' => 'Emphasize your experience with Figma and user research. Highlight your work on design systems and your ability to create user-centered experiences.',
                'is_interested' => true,
                'status' => 'interview',
                'viewed_at' => now()->subDays(2),
                'applied_at' => now()->subDays(1)
            ],
            [
                'post_id' => $posts->where('title', 'Senior Product Designer')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'UX/UI Design Jobs')->first()->id,
                'overall_score' => 85,
                'skills_score' => 88,
                'experience_score' => 80,
                'education_score' => 85,
                'company_fit_score' => 90,
                'strengths' => [
                    'Strong design skills and experience',
                    'Good educational background',
                    'Experience with design systems'
                ],
                'weaknesses' => [
                    'May need more senior-level experience',
                    'Limited experience leading design projects'
                ],
                'missing_skills' => [],
                'matching_skills' => ['Figma', 'Sketch', 'Adobe Creative Suite', 'User Research', 'Design Systems'],
                'match_summary' => 'Good match for this senior product designer position. Emily has strong design skills and experience with design systems, though she may need more leadership experience.',
                'improvement_suggestions' => [
                    'Gain more leadership experience',
                    'Lead design projects from start to finish',
                    'Mentor junior designers'
                ],
                'application_advice' => 'Highlight your experience with design systems and user research. Emphasize your growth potential and willingness to take on leadership responsibilities.',
                'is_interested' => null,
                'status' => 'new'
            ]
        ];

        foreach ($emilyMatches as $match) {
            PostMatch::create(array_merge($match, ['user_profile_id' => $emily->id]));
        }

        // Create matches for David Kim (DevOps Engineer)
        $david = $userProfiles->where('name', 'David Kim')->first();
        $davidMatches = [
            [
                'post_id' => $posts->where('title', 'DevOps Engineer')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'DevOps & Infrastructure')->first()->id,
                'overall_score' => 94,
                'skills_score' => 96,
                'experience_score' => 90,
                'education_score' => 85,
                'company_fit_score' => 92,
                'strengths' => [
                    'Perfect skill match (AWS, Kubernetes, Docker, Terraform)',
                    'Relevant work experience in DevOps',
                    'Strong certifications (AWS, Kubernetes, Terraform)',
                    'Experience with CI/CD pipelines'
                ],
                'weaknesses' => [
                    'Could benefit from more monitoring experience',
                    'Limited experience with Jenkins'
                ],
                'missing_skills' => ['Jenkins'],
                'matching_skills' => ['AWS', 'Kubernetes', 'Docker', 'Terraform', 'Python', 'Bash'],
                'match_summary' => 'Exceptional match for this DevOps engineer position. David has all the required skills and relevant experience in cloud infrastructure and automation.',
                'improvement_suggestions' => [
                    'Gain more experience with Jenkins',
                    'Practice with monitoring tools',
                    'Contribute to infrastructure automation projects'
                ],
                'application_advice' => 'Emphasize your AWS and Kubernetes certifications. Highlight your experience with infrastructure as code and CI/CD pipelines. Mention your track record of automating deployment processes.',
                'is_interested' => true,
                'status' => 'offer',
                'viewed_at' => now()->subDays(3),
                'applied_at' => now()->subDays(2)
            ]
        ];

        foreach ($davidMatches as $match) {
            PostMatch::create(array_merge($match, ['user_profile_id' => $david->id]));
        }

        // Create matches for Lisa Thompson (Product Manager)
        $lisa = $userProfiles->where('name', 'Lisa Thompson')->first();
        $lisaMatches = [
            [
                'post_id' => $posts->where('title', 'Product Manager')->first()->id,
                'search_criteria_id' => $searchCriteria->where('name', 'Product Management')->first()->id,
                'overall_score' => 96,
                'skills_score' => 95,
                'experience_score' => 98,
                'education_score' => 98,
                'company_fit_score' => 95,
                'strengths' => [
                    'Perfect skill match (Product Strategy, Agile, User Research)',
                    'Excellent educational background (Harvard MBA)',
                    'Strong experience in B2B SaaS product management',
                    'Proven track record of increasing user engagement'
                ],
                'weaknesses' => [
                    'May prefer in-person work over remote',
                    'Salary expectations may be at the high end'
                ],
                'missing_skills' => [],
                'matching_skills' => ['Product Strategy', 'Agile/Scrum', 'User Research', 'Data Analysis', 'SQL'],
                'match_summary' => 'Exceptional match for this product manager position. Lisa has all the required skills, excellent education, and proven experience in B2B SaaS product management.',
                'improvement_suggestions' => [
                    'Consider remote work preferences',
                    'Prepare for salary negotiations',
                    'Highlight recent product launches'
                ],
                'application_advice' => 'Emphasize your Harvard MBA and experience with B2B SaaS products. Highlight your success in increasing user engagement by 40%. Mention your experience with cross-functional teams.',
                'is_interested' => true,
                'status' => 'interview',
                'viewed_at' => now()->subDays(1),
                'applied_at' => now()->subHours(12)
            ]
        ];

        foreach ($lisaMatches as $match) {
            PostMatch::create(array_merge($match, ['user_profile_id' => $lisa->id]));
        }
    }
} 