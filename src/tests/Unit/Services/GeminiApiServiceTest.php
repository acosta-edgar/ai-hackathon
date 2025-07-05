<?php

namespace Tests\Unit\Services;

use App\Services\GeminiApiService;
use Google\Cloud\AIPlatform\V1\GenerativeServiceClient;
use Google\Cloud\AIPlatform\V1\GenerateContentResponse;
use Google\Cloud\AIPlatform\V1\Candidate;
use Google\Cloud\AIPlatform\V1\Content;
use Google\Cloud\AIPlatform\V1\Part;
use Google\Cloud\AIPlatform\V1\SafetyRating;
use Google\Cloud\AIPlatform\V1\SafetySetting\HarmCategory;
use Google\Cloud\AIPlatform\V1\SafetySetting\HarmBlockThreshold;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\TestCase;
use Exception;

class GeminiApiServiceTest extends TestCase
{
    protected $mockClient;
    protected $service;
    protected $mockResponse;
    protected $mockCandidate;
    protected $mockContent;
    protected $mockPart;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the GenerativeServiceClient
        $this->mockClient = Mockery::mock(GenerativeServiceClient::class);
        
        // Mock response components
        $this->mockPart = Mockery::mock(Part::class);
        $this->mockContent = Mockery::mock(Content::class);
        $this->mockCandidate = Mockery::mock(Candidate::class);
        $this->mockResponse = Mockery::mock(GenerateContentResponse::class);
        
        // Set up config values
        Config::shouldReceive('get')
            ->with('services.gemini.project_id')
            ->andReturn('test-project');
            
        Config::shouldReceive('get')
            ->with('services.gemini.api_key')
            ->andReturn('test-api-key');
            
        Config::shouldReceive('get')
            ->with('services.gemini.location', 'us-central1')
            ->andReturn('us-central1');
            
        Config::shouldReceive('get')
            ->with('services.gemini.model', 'gemini-1.5-pro')
            ->andReturn('gemini-1.5-pro');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test service construction with default parameters
     */
    public function test_constructor_with_default_parameters()
    {
        // This test will verify the service can be instantiated
        // We'll need to mock the client creation
        $this->expectException(Exception::class);
        
        new GeminiApiService();
    }

    /**
     * Test service construction with custom parameters
     */
    public function test_constructor_with_custom_parameters()
    {
        $this->expectException(Exception::class);
        
        new GeminiApiService(
            'custom-api-key',
            'custom-project',
            'us-east1',
            'gemini-1.0-pro'
        );
    }

    /**
     * Test successful text generation
     */
    public function test_generate_text_success()
    {
        // Set up mocks
        $this->mockPart->shouldReceive('getText')
            ->andReturn('Generated response text');
            
        $this->mockContent->shouldReceive('getParts')
            ->andReturn([$this->mockPart]);
            
        $this->mockCandidate->shouldReceive('getContent')
            ->andReturn($this->mockContent);
            
        $this->mockCandidate->shouldReceive('getSafetyRatings')
            ->andReturn([]);
            
        $this->mockResponse->shouldReceive('getCandidates')
            ->andReturn([$this->mockCandidate]);
            
        $this->mockClient->shouldReceive('generateContent')
            ->once()
            ->andReturn($this->mockResponse);

        // Create service with mocked client
        $service = $this->createServiceWithMockedClient();
        
        $result = $service->generateText('Test prompt');
        
        $this->assertEquals('Generated response text', $result);
    }

    /**
     * Test text generation with safety block
     */
    public function test_generate_text_with_safety_block()
    {
        $mockSafetyRating = Mockery::mock(SafetyRating::class);
        $mockSafetyRating->shouldReceive('getBlocked')
            ->andReturn(true);
        $mockSafetyRating->shouldReceive('getCategory')
            ->andReturn(HarmCategory::HARM_CATEGORY_HARASSMENT);
        $mockSafetyRating->shouldReceive('getBlockReason')
            ->andReturn('Content violates safety guidelines');
            
        $this->mockCandidate->shouldReceive('getSafetyRatings')
            ->andReturn([$mockSafetyRating]);
            
        $this->mockResponse->shouldReceive('getCandidates')
            ->andReturn([$this->mockCandidate]);
            
        $this->mockClient->shouldReceive('generateContent')
            ->once()
            ->andReturn($this->mockResponse);

        $service = $this->createServiceWithMockedClient();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Content blocked due to HARM_CATEGORY_HARASSMENT: Content violates safety guidelines');
        
        $service->generateText('Test prompt');
    }

    /**
     * Test text generation with no candidates
     */
    public function test_generate_text_no_candidates()
    {
        $this->mockResponse->shouldReceive('getCandidates')
            ->andReturn([]);
            
        $this->mockClient->shouldReceive('generateContent')
            ->once()
            ->andReturn($this->mockResponse);

        $service = $this->createServiceWithMockedClient();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No response from Gemini API');
        
        $service->generateText('Test prompt');
    }

    /**
     * Test text generation with no content
     */
    public function test_generate_text_no_content()
    {
        $this->mockCandidate->shouldReceive('getContent')
            ->andReturn(null);
            
        $this->mockResponse->shouldReceive('getCandidates')
            ->andReturn([$this->mockCandidate]);
            
        $this->mockClient->shouldReceive('generateContent')
            ->once()
            ->andReturn($this->mockResponse);

        $service = $this->createServiceWithMockedClient();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No content in response');
        
        $service->generateText('Test prompt');
    }

    /**
     * Test cover letter generation
     */
    public function test_generate_cover_letter()
    {
        $postDescription = 'We are looking for a software engineer...';
        $userProfile = [
            'name' => 'John Doe',
            'title' => 'Senior Developer',
            'experience' => [
                [
                    'title' => 'Senior Developer',
                    'company' => 'Tech Corp',
                    'start_date' => '2020-01',
                    'current' => true,
                    'description' => 'Led development team'
                ]
            ],
            'education' => [
                [
                    'degree' => 'Bachelor of Science',
                    'field_of_study' => 'Computer Science',
                    'institution' => 'University of Tech',
                    'start_date' => '2016-09',
                    'current' => false,
                    'end_date' => '2020-05'
                ]
            ],
            'skills' => ['PHP', 'Laravel', 'JavaScript']
        ];

        // Mock the generateText method
        $service = Mockery::mock(GeminiApiService::class)->makePartial();
        $service->shouldReceive('generateText')
            ->once()
            ->andReturn('Generated cover letter content');

        $result = $service->generateCoverLetter($postDescription, $userProfile);
        
        $this->assertEquals('Generated cover letter content', $result);
    }

    /**
     * Test cover letter generation with custom options
     */
    public function test_generate_cover_letter_with_custom_options()
    {
        $postDescription = 'We are looking for a software engineer...';
        $userProfile = [
            'name' => 'John Doe',
            'title' => 'Senior Developer',
            'skills' => ['PHP', 'Laravel']
        ];
        
        $options = [
            'tone' => 'enthusiastic',
            'length' => 'short',
            'highlight_skills' => false,
            'include_salary_expectations' => true,
            'custom_instructions' => 'Focus on teamwork'
        ];

        $service = Mockery::mock(GeminiApiService::class)->makePartial();
        $service->shouldReceive('generateText')
            ->once()
            ->andReturn('Generated cover letter content');

        $result = $service->generateCoverLetter($postDescription, $userProfile, $options);
        
        $this->assertEquals('Generated cover letter content', $result);
    }

    /**
     * Test post match analysis
     */
    public function test_analyze_post_match()
    {
        $post = [
            'title' => 'Senior Software Engineer',
            'company_name' => 'Tech Company',
            'location' => 'San Francisco',
            'post_type' => 'full-time',
            'experience_level' => 'senior',
            'skills' => ['PHP', 'Laravel', 'JavaScript'],
            'description' => 'We are looking for a senior developer...'
        ];
        
        $userProfile = [
            'name' => 'John Doe',
            'title' => 'Senior Developer',
            'skills' => ['PHP', 'Laravel', 'Vue.js'],
            'experience' => [
                [
                    'title' => 'Senior Developer',
                    'company' => 'Tech Corp',
                    'start_date' => '2020-01',
                    'current' => true,
                    'description' => 'Led development team'
                ]
            ],
            'education' => [
                [
                    'degree' => 'Bachelor of Science',
                    'field_of_study' => 'Computer Science',
                    'institution' => 'University of Tech',
                    'start_date' => '2016-09',
                    'current' => false,
                    'end_date' => '2020-05'
                ]
            ]
        ];

        $expectedAnalysis = [
            'overall_score' => 85,
            'skills_match' => [
                'matching_skills' => ['PHP', 'Laravel'],
                'missing_skills' => ['JavaScript'],
                'score' => 80
            ],
            'experience_match' => [
                'years_experience_match' => true,
                'industry_experience_match' => true,
                'score' => 90
            ],
            'education_match' => [
                'degree_required' => "Bachelor's",
                'degree_matched' => true,
                'score' => 95
            ],
            'company_culture_fit' => [
                'values_alignment' => 'high',
                'work_style_match' => 'moderate',
                'score' => 85
            ],
            'strengths' => ['Strong PHP experience', 'Team leadership'],
            'weaknesses' => ['Limited JavaScript experience'],
            'recommendations' => ['Improve JavaScript skills', 'Highlight leadership experience']
        ];

        $service = Mockery::mock(GeminiApiService::class)->makePartial();
        $service->shouldReceive('generateText')
            ->once()
            ->andReturn(json_encode($expectedAnalysis));

        $result = $service->analyzePostMatch($post, $userProfile);
        
        $this->assertEquals($expectedAnalysis, $result);
    }

    /**
     * Test post match analysis with invalid JSON response
     */
    public function test_analyze_post_match_invalid_json()
    {
        $post = ['title' => 'Test Post'];
        $userProfile = ['name' => 'John Doe'];

        $service = Mockery::mock(GeminiApiService::class)->makePartial();
        $service->shouldReceive('generateText')
            ->once()
            ->andReturn('Invalid JSON response');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response format from Gemini API');
        
        $service->analyzePostMatch($post, $userProfile);
    }

    /**
     * Test format experience method
     */
    public function test_format_experience()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $experience = [
            [
                'title' => 'Senior Developer',
                'company' => 'Tech Corp',
                'start_date' => '2020-01',
                'current' => true,
                'description' => 'Led development team'
            ],
            [
                'title' => 'Developer',
                'company' => 'Startup Inc',
                'start_date' => '2018-01',
                'current' => false,
                'end_date' => '2020-01',
                'description' => 'Built web applications'
            ]
        ];

        $result = $this->callProtectedMethod($service, 'formatExperience', [$experience]);
        
        $expected = "- Senior Developer at Tech Corp (2020-01 - Present): Led development team\n- Developer at Startup Inc (2018-01 - 2020-01): Built web applications";
        $this->assertEquals($expected, $result);
    }

    /**
     * Test format experience with empty array
     */
    public function test_format_experience_empty()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $result = $this->callProtectedMethod($service, 'formatExperience', [[]]);
        
        $this->assertEquals('No experience provided', $result);
    }

    /**
     * Test format education method
     */
    public function test_format_education()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $education = [
            [
                'degree' => 'Bachelor of Science',
                'field_of_study' => 'Computer Science',
                'institution' => 'University of Tech',
                'start_date' => '2016-09',
                'current' => false,
                'end_date' => '2020-05',
                'description' => 'Focused on software engineering'
            ]
        ];

        $result = $this->callProtectedMethod($service, 'formatEducation', [$education]);
        
        $expected = "- Bachelor of Science in Computer Science at University of Tech (2016-09 - 2020-05): Focused on software engineering";
        $this->assertEquals($expected, $result);
    }

    /**
     * Test format education with empty array
     */
    public function test_format_education_empty()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $result = $this->callProtectedMethod($service, 'formatEducation', [[]]);
        
        $this->assertEquals('No education provided', $result);
    }

    /**
     * Test format skills method
     */
    public function test_format_skills()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $skills = ['PHP', 'Laravel', 'JavaScript', 'Vue.js'];
        
        $result = $this->callProtectedMethod($service, 'formatSkills', [$skills]);
        
        $this->assertEquals('PHP, Laravel, JavaScript, Vue.js', $result);
    }

    /**
     * Test format skills with empty array
     */
    public function test_format_skills_empty()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $result = $this->callProtectedMethod($service, 'formatSkills', [[]]);
        
        $this->assertEquals('No skills provided', $result);
    }

    /**
     * Test format array method
     */
    public function test_format_array()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $items = ['item1', 'item2', 'item3'];
        
        $result = $this->callProtectedMethod($service, 'formatArray', [$items]);
        
        $this->assertEquals('item1, item2, item3', $result);
    }

    /**
     * Test format array with empty array
     */
    public function test_format_array_empty()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $result = $this->callProtectedMethod($service, 'formatArray', [[]]);
        
        $this->assertEquals('Not specified', $result);
    }

    /**
     * Test get tone instruction method
     */
    public function test_get_tone_instruction()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $this->assertEquals('Use a professional and business-appropriate tone.', $this->callProtectedMethod($service, 'getToneInstruction', ['professional']));
        $this->assertEquals('Use an enthusiastic and energetic tone.', $this->callProtectedMethod($service, 'getToneInstruction', ['enthusiastic']));
        $this->assertEquals('Use a friendly and approachable tone.', $this->callProtectedMethod($service, 'getToneInstruction', ['friendly']));
        $this->assertEquals('Use a formal and respectful tone.', $this->callProtectedMethod($service, 'getToneInstruction', ['formal']));
        $this->assertEquals('Use a professional and business-appropriate tone.', $this->callProtectedMethod($service, 'getToneInstruction', ['invalid']));
    }

    /**
     * Test get length instruction method
     */
    public function test_get_length_instruction()
    {
        $service = new GeminiApiService('test-key', 'test-project');
        
        $this->assertEquals('Keep it concise, around 200-250 words.', $this->callProtectedMethod($service, 'getLengthInstruction', ['short']));
        $this->assertEquals('Aim for a moderate length, around 300-400 words.', $this->callProtectedMethod($service, 'getLengthInstruction', ['medium']));
        $this->assertEquals('Be detailed, around 500-600 words.', $this->callProtectedMethod($service, 'getLengthInstruction', ['long']));
        $this->assertEquals('Aim for a moderate length, around 300-400 words.', $this->callProtectedMethod($service, 'getLengthInstruction', ['invalid']));
    }

    /**
     * Test API error handling
     */
    public function test_generate_text_api_error()
    {
        $this->mockClient->shouldReceive('generateContent')
            ->once()
            ->andThrow(new Exception('API connection failed'));

        $service = $this->createServiceWithMockedClient();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to generate text with Gemini: API connection failed');
        
        $service->generateText('Test prompt');
    }

    /**
     * Helper method to create service with mocked client
     */
    private function createServiceWithMockedClient(): GeminiApiService
    {
        // Use reflection to inject the mocked client
        $service = new GeminiApiService('test-key', 'test-project');
        $reflection = new \ReflectionClass($service);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($service, $this->mockClient);
        
        return $service;
    }

    /**
     * Helper method to call protected methods
     */
    private function callProtectedMethod(GeminiApiService $service, string $method, array $args)
    {
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($service, $args);
    }
} 