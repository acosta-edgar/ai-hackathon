<?php

namespace Database\Seeders;

use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@email.com',
                'phone' => '+1-555-0123',
                'location' => 'San Francisco, CA',
                'title' => 'Senior Full Stack Developer',
                'summary' => 'Experienced full-stack developer with 5+ years building scalable web applications using modern technologies. Passionate about clean code, user experience, and continuous learning.',
                'skills' => [
                    'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Node.js', 'Python', 'Django', 'PostgreSQL', 'MongoDB', 'AWS', 'Docker', 'Git'
                ],
                'experience' => [
                    [
                        'title' => 'Senior Full Stack Developer',
                        'company' => 'TechCorp Inc.',
                        'duration' => '2021 - Present',
                        'description' => 'Led development of customer-facing web applications using React and Node.js. Mentored junior developers and implemented CI/CD pipelines.'
                    ],
                    [
                        'title' => 'Full Stack Developer',
                        'company' => 'StartupXYZ',
                        'duration' => '2019 - 2021',
                        'description' => 'Built and maintained multiple web applications using Vue.js and Python. Collaborated with design and product teams.'
                    ]
                ],
                'education' => [
                    [
                        'degree' => 'Bachelor of Science in Computer Science',
                        'institution' => 'University of California, Berkeley',
                        'year' => '2019'
                    ]
                ],
                'certifications' => [
                    'AWS Certified Developer Associate',
                    'Google Cloud Professional Developer'
                ],
                'languages' => ['English', 'Spanish'],
                'linkedin_url' => 'https://linkedin.com/in/sarahjohnson',
                'github_url' => 'https://github.com/sarahjohnson',
                'preferences' => [
                    'remote_work' => true,
                    'salary_min' => 120000,
                    'salary_max' => 180000,
                    'preferred_locations' => ['San Francisco', 'New York', 'Remote'],
                    'preferred_industries' => ['Technology', 'Fintech', 'Healthcare']
                ]
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@email.com',
                'phone' => '+1-555-0456',
                'location' => 'New York, NY',
                'title' => 'Data Scientist',
                'summary' => 'Data scientist with expertise in machine learning, statistical analysis, and big data processing. Experience in building predictive models and data pipelines.',
                'skills' => [
                    'Python', 'R', 'SQL', 'TensorFlow', 'PyTorch', 'Scikit-learn', 'Pandas', 'NumPy', 'Apache Spark', 'AWS', 'Docker', 'Git'
                ],
                'experience' => [
                    [
                        'title' => 'Data Scientist',
                        'company' => 'DataTech Solutions',
                        'duration' => '2020 - Present',
                        'description' => 'Developed machine learning models for customer segmentation and predictive analytics. Reduced customer churn by 25%.'
                    ],
                    [
                        'title' => 'Junior Data Analyst',
                        'company' => 'Analytics Corp',
                        'duration' => '2018 - 2020',
                        'description' => 'Performed data analysis and created dashboards for business stakeholders. Automated reporting processes.'
                    ]
                ],
                'education' => [
                    [
                        'degree' => 'Master of Science in Data Science',
                        'institution' => 'Columbia University',
                        'year' => '2018'
                    ],
                    [
                        'degree' => 'Bachelor of Science in Statistics',
                        'institution' => 'New York University',
                        'year' => '2016'
                    ]
                ],
                'certifications' => [
                    'Google Cloud Professional Data Engineer',
                    'AWS Certified Machine Learning Specialist'
                ],
                'languages' => ['English', 'Mandarin'],
                'linkedin_url' => 'https://linkedin.com/in/michaelchen',
                'github_url' => 'https://github.com/michaelchen',
                'preferences' => [
                    'remote_work' => true,
                    'salary_min' => 100000,
                    'salary_max' => 150000,
                    'preferred_locations' => ['New York', 'San Francisco', 'Boston', 'Remote'],
                    'preferred_industries' => ['Technology', 'Finance', 'Healthcare']
                ]
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@email.com',
                'phone' => '+1-555-0789',
                'location' => 'Austin, TX',
                'title' => 'UX/UI Designer',
                'summary' => 'Creative UX/UI designer with 4+ years of experience creating user-centered digital experiences. Skilled in user research, wireframing, and prototyping.',
                'skills' => [
                    'Figma', 'Sketch', 'Adobe Creative Suite', 'InVision', 'User Research', 'Wireframing', 'Prototyping', 'Design Systems', 'HTML', 'CSS', 'JavaScript'
                ],
                'experience' => [
                    [
                        'title' => 'Senior UX Designer',
                        'company' => 'Design Studio Pro',
                        'duration' => '2021 - Present',
                        'description' => 'Led design projects for major clients, conducted user research, and created comprehensive design systems.'
                    ],
                    [
                        'title' => 'UX Designer',
                        'company' => 'Creative Agency',
                        'duration' => '2019 - 2021',
                        'description' => 'Designed user interfaces for web and mobile applications. Collaborated with development teams.'
                    ]
                ],
                'education' => [
                    [
                        'degree' => 'Bachelor of Fine Arts in Graphic Design',
                        'institution' => 'University of Texas at Austin',
                        'year' => '2019'
                    ]
                ],
                'certifications' => [
                    'Google UX Design Certificate',
                    'Nielsen Norman Group UX Certification'
                ],
                'languages' => ['English', 'Spanish'],
                'linkedin_url' => 'https://linkedin.com/in/emilyrodriguez',
                'github_url' => 'https://github.com/emilyrodriguez',
                'preferences' => [
                    'remote_work' => true,
                    'salary_min' => 80000,
                    'salary_max' => 120000,
                    'preferred_locations' => ['Austin', 'San Francisco', 'New York', 'Remote'],
                    'preferred_industries' => ['Technology', 'Design', 'E-commerce']
                ]
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.kim@email.com',
                'phone' => '+1-555-0321',
                'location' => 'Seattle, WA',
                'title' => 'DevOps Engineer',
                'summary' => 'DevOps engineer with expertise in cloud infrastructure, CI/CD pipelines, and infrastructure as code. Experience with AWS, Kubernetes, and automation.',
                'skills' => [
                    'AWS', 'Azure', 'Kubernetes', 'Docker', 'Terraform', 'Ansible', 'Jenkins', 'GitLab CI', 'Python', 'Bash', 'Linux', 'Monitoring'
                ],
                'experience' => [
                    [
                        'title' => 'DevOps Engineer',
                        'company' => 'CloudTech Solutions',
                        'duration' => '2020 - Present',
                        'description' => 'Managed cloud infrastructure, implemented CI/CD pipelines, and automated deployment processes.'
                    ],
                    [
                        'title' => 'System Administrator',
                        'company' => 'IT Services Inc.',
                        'duration' => '2018 - 2020',
                        'description' => 'Maintained server infrastructure and provided technical support to development teams.'
                    ]
                ],
                'education' => [
                    [
                        'degree' => 'Bachelor of Science in Information Technology',
                        'institution' => 'University of Washington',
                        'year' => '2018'
                    ]
                ],
                'certifications' => [
                    'AWS Certified Solutions Architect',
                    'Kubernetes Administrator (CKA)',
                    'Terraform Associate'
                ],
                'languages' => ['English', 'Korean'],
                'linkedin_url' => 'https://linkedin.com/in/davidkim',
                'github_url' => 'https://github.com/davidkim',
                'preferences' => [
                    'remote_work' => true,
                    'salary_min' => 110000,
                    'salary_max' => 160000,
                    'preferred_locations' => ['Seattle', 'San Francisco', 'Remote'],
                    'preferred_industries' => ['Technology', 'Cloud Services', 'E-commerce']
                ]
            ],
            [
                'name' => 'Lisa Thompson',
                'email' => 'lisa.thompson@email.com',
                'phone' => '+1-555-0654',
                'location' => 'Boston, MA',
                'title' => 'Product Manager',
                'summary' => 'Product manager with 6+ years of experience in software product development. Skilled in agile methodologies, user research, and cross-functional team leadership.',
                'skills' => [
                    'Product Strategy', 'Agile/Scrum', 'User Research', 'Data Analysis', 'A/B Testing', 'Roadmapping', 'Stakeholder Management', 'SQL', 'Jira', 'Figma'
                ],
                'experience' => [
                    [
                        'title' => 'Senior Product Manager',
                        'company' => 'ProductCorp',
                        'duration' => '2021 - Present',
                        'description' => 'Led product strategy and development for B2B SaaS platform. Increased user engagement by 40%.'
                    ],
                    [
                        'title' => 'Product Manager',
                        'company' => 'Tech Startup',
                        'duration' => '2019 - 2021',
                        'description' => 'Managed product lifecycle from ideation to launch. Collaborated with engineering and design teams.'
                    ]
                ],
                'education' => [
                    [
                        'degree' => 'Master of Business Administration',
                        'institution' => 'Harvard Business School',
                        'year' => '2019'
                    ],
                    [
                        'degree' => 'Bachelor of Science in Engineering',
                        'institution' => 'MIT',
                        'year' => '2017'
                    ]
                ],
                'certifications' => [
                    'Certified Scrum Product Owner (CSPO)',
                    'Google Analytics Individual Qualification'
                ],
                'languages' => ['English', 'French'],
                'linkedin_url' => 'https://linkedin.com/in/lisathompson',
                'github_url' => 'https://github.com/lisathompson',
                'preferences' => [
                    'remote_work' => false,
                    'salary_min' => 120000,
                    'salary_max' => 180000,
                    'preferred_locations' => ['Boston', 'New York', 'San Francisco'],
                    'preferred_industries' => ['Technology', 'SaaS', 'Fintech']
                ]
            ]
        ];

        foreach ($profiles as $profile) {
            UserProfile::create($profile);
        }
    }
} 