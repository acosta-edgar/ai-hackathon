<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PostBoardRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'requires_authentication' => 'required|boolean',
            'authentication_details' => 'nullable|array',
            'authentication_details.username' => 'required_if:requires_authentication,true|string|max:255',
            'authentication_details.password' => 'required_if:requires_authentication,true|string|max:255',
            'authentication_details.api_key' => 'nullable|string|max:255',
            'search_parameters' => 'nullable|array',
            'search_parameters.keywords_param' => 'nullable|string|max:50',
            'search_parameters.location_param' => 'nullable|string|max:50',
            'search_parameters.post_type_param' => 'nullable|string|max:50',
            'search_parameters.page_param' => 'nullable|string|max:50',
            'search_parameters.per_page_param' => 'nullable|string|max:50',
            'search_parameters.sort_param' => 'nullable|string|max:50',
            'search_frequency_hours' => 'nullable|integer|min:1|max:744', // Max 31 days
            'is_active' => 'required|boolean',
        ];

        // For update operations, make fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $optionalRules = [];
            foreach ($rules as $field => $rule) {
                if (!in_array('required', explode('|', $rule)) && !str_contains($rule, 'required_if')) {
                    $optionalRules[$field] = 'sometimes|' . $rule;
                } else {
                    $optionalRules[$field] = $rule;
                }
            }
            return $optionalRules;
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Ensure boolean fields are properly cast
        $this->merge([
            'requires_authentication' => $this->boolean('requires_authentication'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'authentication_details.username.required_if' => 'Username is required when authentication is enabled.',
            'authentication_details.password.required_if' => 'Password is required when authentication is enabled.',
            'search_frequency_hours.max' => 'Search frequency cannot be more than 744 hours (31 days).',
        ];
    }
}
