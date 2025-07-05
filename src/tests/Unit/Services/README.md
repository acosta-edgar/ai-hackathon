# GeminiApiService Unit Tests

This directory contains comprehensive unit tests for the `GeminiApiService` class.

## Test Coverage

The tests cover the following functionality:

### Core API Methods
- ✅ `generateText()` - Text generation with various scenarios
- ✅ `generateCoverLetter()` - Cover letter generation with options
- ✅ `analyzePostMatch()` - Post match analysis

### Helper Methods
- ✅ `formatExperience()` - Experience formatting
- ✅ `formatEducation()` - Education formatting
- ✅ `formatSkills()` - Skills formatting
- ✅ `formatArray()` - Array formatting
- ✅ `getToneInstruction()` - Tone instruction mapping
- ✅ `getLengthInstruction()` - Length instruction mapping

### Error Handling
- ✅ API connection failures
- ✅ Safety content blocking
- ✅ Invalid JSON responses
- ✅ Missing content scenarios
- ✅ Empty response handling

## Running the Tests

### Run All Unit Tests
```bash
php artisan test --filter=GeminiApiServiceTest
```

### Run Specific Test Methods
```bash
# Test text generation
php artisan test --filter=test_generate_text_success

# Test cover letter generation
php artisan test --filter=test_generate_cover_letter

# Test post match analysis
php artisan test --filter=test_analyze_post_match
```

### Run with Coverage
```bash
php artisan test --filter=GeminiApiServiceTest --coverage
```

## Test Structure

### Mocking Strategy
- **GenerativeServiceClient**: Mocked to avoid actual API calls
- **Config Facade**: Mocked to provide test configuration
- **Log Facade**: Available for error logging verification
- **Response Objects**: Mocked to simulate various API responses

### Test Data
- Realistic job post data
- User profile information
- Various error scenarios
- Edge cases (empty arrays, invalid data)

## Key Test Scenarios

### 1. Text Generation
- ✅ Successful generation
- ✅ Safety content blocking
- ✅ No candidates returned
- ✅ No content in response
- ✅ API connection errors

### 2. Cover Letter Generation
- ✅ Basic cover letter generation
- ✅ Custom options (tone, length, etc.)
- ✅ Different user profile structures

### 3. Post Match Analysis
- ✅ Successful analysis with JSON response
- ✅ Invalid JSON response handling
- ✅ Complex post and profile data

### 4. Helper Methods
- ✅ Formatting methods with various inputs
- ✅ Empty array handling
- ✅ Edge cases

## Dependencies

The tests require:
- **Mockery**: For mocking external dependencies
- **PHPUnit**: For test framework
- **Reflection**: For accessing protected methods

## Configuration

The tests use mock configuration values:
- Project ID: `test-project`
- API Key: `test-api-key`
- Location: `us-central1`
- Model: `gemini-1.5-pro`

## Best Practices

1. **Isolation**: Each test is independent and doesn't rely on external services
2. **Mocking**: External dependencies are properly mocked
3. **Coverage**: All public methods and major code paths are tested
4. **Edge Cases**: Error scenarios and edge cases are covered
5. **Readability**: Tests are well-documented and easy to understand

## Troubleshooting

### Common Issues

1. **Mockery Errors**: Ensure Mockery is properly closed in tearDown
2. **Reflection Errors**: Protected methods are accessed via reflection helper
3. **Configuration Errors**: Config facade is mocked for all required values

### Debug Mode

Run tests with verbose output:
```bash
php artisan test --filter=GeminiApiServiceTest --verbose
```

## Integration with CI/CD

Add to your CI/CD pipeline:
```yaml
- name: Run Unit Tests
  run: php artisan test --filter=GeminiApiServiceTest --coverage-html coverage
``` 