<?php

namespace App\Services;

use Google\Cloud\AIPlatform\V1\Content;
use Google\Cloud\AIPlatform\V1\Content as GeminiContent;
use Google\Cloud\AIPlatform\V1\GenerativeServiceClient;
use Google\Cloud\AIPlatform\V1\GenerationConfig;
use Google\Cloud\AIPlatform\V1\Part;
use Google\Cloud\AIPlatform\V1\SafetySetting;
use Google\Cloud\AIPlatform\V1\Tool;
use Google\Cloud\AIPlatform\V1\FunctionDeclaration;
use Google\Cloud\AIPlatform\V1\Type\StructType;
use Google\Cloud\AIPlatform\V1\Type\Type;
use Google\Protobuf\Value;
use Google\Protobuf\ListValue;
use Google\Protobuf\Struct;
use Google\Protobuf\Value as ProtobufValue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiApiService
{
    /**
     * The API client instance.
     *
     * @var \Google\Cloud\AIPlatform\V1\GenerativeServiceClient
     */
    protected $client;

    /**
     * The project ID.
     *
     * @var string
     */
    protected $projectId;

    /**
     * The location.
     *
     * @var string
     */
    protected $location = 'us-central1';

    /**
     * The model name.
     *
     * @var string
     */
    protected $model = 'gemini-1.5-pro';

    /**
     * The model endpoint.
     *
     * @var string
     */
    protected $modelEndpoint;

    /**
     * The generation config.
     *
     * @var \Google\Cloud\AIPlatform\V1\GenerationConfig
     */
    protected $generationConfig;

    /**
     * The safety settings.
     *
     * @var array
     */
    protected $safetySettings = [];

    /**
     * Create a new Gemini API service instance.
     *
     * @param  string|null  $apiKey
     * @param  string|null  $projectId
     * @param  string|null  $location
     * @param  string|null  $model
     * @return void
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $projectId = null,
        ?string $location = null,
        ?string $model = null
    ) {
        $this->projectId = $projectId ?: Config::get('services.gemini.project_id');
        $apiKey = $apiKey ?: Config::get('services.gemini.api_key');
        $this->location = $location ?: Config::get('services.gemini.location', 'us-central1');
        $this->model = $model ?: Config::get('services.gemini.model', 'gemini-1.5-pro');
        
        $this->modelEndpoint = sprintf(
            'projects/%s/locations/%s/publishers/google/models/%s',
            $this->projectId,
            $this->location,
            $this->model
        );

        // Initialize the client with API key
        $options = [
            'apiKey' => $apiKey,
            'transportConfig' => [
                'rest' => [
                    'restClientConfigPath' => __DIR__ . '/../../storage/rest-config.json',
                ],
            ],
        ];

        $this->client = new GenerativeServiceClient($options);
        
        // Set up default generation config
        $this->generationConfig = (new GenerationConfig())
            ->setTemperature(0.2)
            ->setTopP(0.8)
            ->setTopK(40)
            ->setMaxOutputTokens(2048);

        // Set up default safety settings
        $this->setDefaultSafetySettings();
    }

    /**
     * Set default safety settings.
     *
     * @return void
     */
    protected function setDefaultSafetySettings(): void
    {
        $this->safetySettings = [
            (new SafetySetting())
                ->setCategory(SafetySetting\HarmCategory::HARM_CATEGORY_HARASSMENT)
                ->setThreshold(SafetySetting\HarmBlockThreshold::BLOCK_ONLY_HIGH),
            (new SafetySetting())
                ->setCategory(SafetySetting\HarmCategory::HARM_CATEGORY_HATE_SPEECH)
                ->setThreshold(SafetySetting\HarmBlockThreshold::BLOCK_ONLY_HIGH),
            (new SafetySetting())
                ->setCategory(SafetySetting\HarmCategory::HARM_CATEGORY_SEXUALLY_EXPLICIT)
                ->setThreshold(SafetySetting\HarmBlockThreshold::BLOCK_ONLY_HIGH),
            (new SafetySetting())
                ->setCategory(SafetySetting\HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT)
                ->setThreshold(SafetySetting\HarmBlockThreshold::BLOCK_ONLY_HIGH),
        ];
    }

    /**
     * Generate text content.
     *
     * @param  string  $prompt
     * @param  array  $parameters
     * @return string
     * @throws \Exception
     */
    public function generateText(string $prompt, array $parameters = []): string
    {
        try {
            $content = (new GeminiContent())
                ->setRole('user')
                ->setParts([new Part(['text' => $prompt])]);

            $response = $this->client->generateContent([
                'model' => $this->modelEndpoint,
                'contents' => [$content],
                'generationConfig' => $this->generationConfig,
                'safetySettings' => $this->safetySettings,
            ]);

            $candidates = $response->getCandidates();
            
            if (empty($candidates)) {
                throw new Exception('No response from Gemini API');
            }

            $candidate = $candidates[0];
            
            // Check for safety ratings
            if ($candidate->getSafetyRatings()) {
                foreach ($candidate->getSafetyRatings() as $rating) {
                    if ($rating->getBlocked()) {
                        throw new Exception(sprintf(
                            'Content blocked due to %s: %s',
                            $rating->getCategory(),
                            $rating->getBlockReason()
                        ));
                    }
                }
            }

            $content = $candidate->getContent();
            
            if (!$content || !$content->getParts()) {
                throw new Exception('No content in response');
            }

            $parts = $content->getParts();
            $textParts = [];
            
            foreach ($parts as $part) {
                if ($part->getText() !== '') {
                    $textParts[] = $part->getText();
                }
            }

            return implode('\n', $textParts);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'prompt' => $prompt,
            ]);
            
            throw new Exception('Failed to generate text with Gemini: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Generate a cover letter.
     *
     * @param  string  $postDescription
     * @param  array   $userProfile
     * @param  array   $options
     * @return string
     * @throws \Exception
     */
    public function generateCoverLetter(
        string $postDescription,
        array $userProfile,
        array $options = []
    ): string {
        $defaultOptions = [
            'tone' => 'professional', // professional, enthusiastic, friendly, formal
            'length' => 'medium', // short, medium, long
            'highlight_skills' => true,
            'include_salary_expectations' => false,
            'custom_instructions' => '',
        ];

        $options = array_merge($defaultOptions, $options);

        $prompt = $this->buildCoverLetterPrompt(
            $postDescription,
            $userProfile,
            $options
        );

        return $this->generateText($prompt);
    }

    /**
     * Build the cover letter prompt.
     *
     * @param  string  $postDescription
     * @param  array   $userProfile
     * @param  array   $options
     * @return string
     */
    protected function buildCoverLetterPrompt(
        string $postDescription,
        array $userProfile,
        array $options
    ): string {
        $toneInstruction = $this->getToneInstruction($options['tone']);
        $lengthInstruction = $this->getLengthInstruction($options['length']);
        
        $skillsInstruction = $options['highlight_skills'] 
            ? "Highlight the most relevant skills from my profile that match the post requirements." 
            : "";
            
        $salaryInstruction = $options['include_salary_expectations']
            ? "Include salary expectations based on my experience and the post's requirements."
            : "Do not mention salary expectations unless specifically asked in the post description.";

        return <<<PROMPT
        Write a {$options['tone']} cover letter based on the following post description and my profile.
        
        Post Description:
        {$postDescription}
        
        My Profile:
        - Name: {$userProfile['name']}
        - Title: {$userProfile['title']}
        - Experience: {$this->formatExperience($userProfile['experience'] ?? [])}
        - Education: {$this->formatEducation($userProfile['education'] ?? [])}
        - Skills: {$this->formatSkills($userProfile['skills'] ?? [])}
        
        Instructions:
        - {$toneInstruction}
        - {$lengthInstruction}
        - {$skillsInstruction}
        - {$salaryInstruction}
        - Focus on how my skills and experience align with the post requirements.
        - Use a professional business letter format.
        - Do not include any placeholders - generate complete content.
        - {$options['custom_instructions']}
        
        Generate only the content of the cover letter, without any additional explanations or notes.
        PROMPT;
    }

    /**
     * Analyze post match.
     *
     * @param  array  $post
     * @param  array  $userProfile
     * @param  array  $searchCriteria
     * @return array
     * @throws \Exception
     */
    public function analyzePostMatch(
        array $post,
        array $userProfile,
        array $searchCriteria = []
    ): array {
        $prompt = $this->buildMatchAnalysisPrompt($post, $userProfile, $searchCriteria);
        
        // Update generation config for more structured output
        $this->generationConfig->setTemperature(0.1);
        
        $response = $this->generateText($prompt);
        
        // Reset generation config
        $this->generationConfig->setTemperature(0.2);
        
        return $this->parseMatchAnalysisResponse($response);
    }

    /**
     * Build the post match analysis prompt.
     *
     * @param  array  $post
     * @param  array  $userProfile
     * @param  array  $searchCriteria
     * @return string
     */
    protected function buildMatchAnalysisPrompt(
        array $post,
        array $userProfile,
        array $searchCriteria = []
    ): string {
        return <<<PROMPT
        Analyze the match between the following post and candidate profile.
        
        Post Details:
        - Title: {$post['title']}
        - Company: {$post['company_name']}
        - Location: {$post['location']}
        - Job Type: {$post['post_type'] ?? 'Not specified'}
        - Experience Level: {$post['experience_level'] ?? 'Not specified'}
        - Skills: {$this->formatSkills($post['skills'] ?? [])}
        - Description: {$post['description']}
        
        Candidate Profile:
        - Name: {$userProfile['name']}
        - Title: {$userProfile['title']}
        - Experience: {$this->formatExperience($userProfile['experience'] ?? [])}
        - Education: {$this->formatEducation($userProfile['education'] ?? [])}
        - Skills: {$this->formatSkills($userProfile['skills'] ?? [])}
        
        Search Criteria (if applicable):
        - Keywords: {$this->formatArray($searchCriteria['keywords'] ?? [])}
        - Locations: {$this->formatArray($searchCriteria['locations'] ?? [])}
        - Job Types: {$this->formatArray($searchCriteria['post_types'] ?? [])}
        - Experience Levels: {$this->formatArray($searchCriteria['experience_levels'] ?? [])}
        
        Please provide a detailed analysis including:
        1. Overall match score (0-100)
        2. Skills match (list matching and missing skills)
        3. Experience match
        4. Education match
        5. Company culture fit
        6. Key strengths for this role
        7. Potential weaknesses or gaps
        8. Recommendations for improving the application
        
        Format your response as a JSON object with the following structure:
        {
            "overall_score": 85,
            "skills_match": {
                "matching_skills": ["skill1", "skill2"],
                "missing_skills": ["skill3", "skill4"],
                "score": 80
            },
            "experience_match": {
                "years_experience_match": true,
                "industry_experience_match": true,
                "score": 90
            },
            "education_match": {
                "degree_required": "Bachelor's",
                "degree_matched": true,
                "score": 95
            },
            "company_culture_fit": {
                "values_alignment": "high",
                "work_style_match": "moderate",
                "score": 85
            },
            "strengths": ["strength1", "strength2"],
            "weaknesses": ["weakness1", "weakness2"],
            "recommendations": ["recommendation1", "recommendation2"]
        }
        
        Provide only the JSON response, without any additional text or explanations.
        PROMPT;
    }

    /**
     * Parse the match analysis response.
     *
     * @param  string  $response
     * @return array
     */
    protected function parseMatchAnalysisResponse(string $response): array
    {
        // Clean the response to extract just the JSON
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new Exception('Invalid response format from Gemini API');
        }
        
        $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse JSON response: ' . json_last_error_msg());
        }
        
        return $data;
    }

    /**
     * Format experience for the prompt.
     *
     * @param  array  $experience
     * @return string
     */
    protected function formatExperience(array $experience): string
    {
        if (empty($experience)) {
            return 'No experience provided';
        }
        
        $formatted = [];
        
        foreach ($experience as $exp) {
            $formattedExp = "- {$exp['title']} at {$exp['company']}";
            $formattedExp .= " ({$exp['start_date']} - " . ($exp['current'] ? 'Present' : $exp['end_date']) . ")";
            
            if (!empty($exp['description'])) {
                $formattedExp .= ": {$exp['description']}";
            }
            
            $formatted[] = $formattedExp;
        }
        
        return implode("\n", $formatted);
    }

    /**
     * Format education for the prompt.
     *
     * @param  array  $education
     * @return string
     */
    protected function formatEducation(array $education): string
    {
        if (empty($education)) {
            return 'No education provided';
        }
        
        $formatted = [];
        
        foreach ($education as $edu) {
            $formattedEdu = "- {$edu['degree']} in {$edu['field_of_study']} at {$edu['institution']}";
            $formattedEdu .= " ({$edu['start_date']} - " . ($edu['current'] ? 'Present' : $edu['end_date']) . ")";
            
            if (!empty($edu['description'])) {
                $formattedEdu .= ": {$edu['description']}";
            }
            
            $formatted[] = $formattedEdu;
        }
        
        return implode("\n", $formatted);
    }

    /**
     * Format skills for the prompt.
     *
     * @param  array  $skills
     * @return string
     */
    protected function formatSkills(array $skills): string
    {
        if (empty($skills)) {
            return 'No skills provided';
        }
        
        return implode(', ', $skills);
    }

    /**
     * Format an array as a comma-separated string.
     *
     * @param  array  $items
     * @return string
     */
    protected function formatArray(array $items): string
    {
        if (empty($items)) {
            return 'Not specified';
        }
        
        return implode(', ', $items);
    }

    /**
     * Get tone instruction.
     *
     * @param  string  $tone
     * @return string
     */
    protected function getToneInstruction(string $tone): string
    {
        $tones = [
            'professional' => 'Use a professional and business-appropriate tone.',
            'enthusiastic' => 'Use an enthusiastic and energetic tone.',
            'friendly' => 'Use a friendly and approachable tone.',
            'formal' => 'Use a formal and respectful tone.',
        ];
        
        return $tones[strtolower($tone)] ?? $tones['professional'];
    }

    /**
     * Get length instruction.
     *
     * @param  string  $length
     * @return string
     */
    protected function getLengthInstruction(string $length): string
    {
        $lengths = [
            'short' => 'Keep it concise, around 200-250 words.',
            'medium' => 'Aim for a moderate length, around 300-400 words.',
            'long' => 'Be detailed, around 500-600 words.',
        ];
        
        return $lengths[strtolower($length)] ?? $lengths['medium'];
    }
}
