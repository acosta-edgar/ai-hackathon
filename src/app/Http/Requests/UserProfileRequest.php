<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UserProfileRequest extends ApiRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_profiles', 'email')->ignore($this->route('user_profile'))
            ],
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'experience' => 'nullable|array',
            'experience.*.title' => 'required|string|max:255',
            'experience.*.company' => 'required|string|max:255',
            'experience.*.location' => 'nullable|string|max:255',
            'experience.*.start_date' => 'required|date',
            'experience.*.end_date' => 'nullable|date|after:experience.*.start_date',
            'experience.*.current' => 'boolean',
            'experience.*.description' => 'nullable|string',
            'education' => 'nullable|array',
            'education.*.institution' => 'required|string|max:255',
            'education.*.degree' => 'required|string|max:255',
            'education.*.field_of_study' => 'required|string|max:255',
            'education.*.start_date' => 'required|date',
            'education.*.end_date' => 'nullable|date|after:education.*.start_date',
            'education.*.current' => 'boolean',
            'education.*.description' => 'nullable|string',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required|string|max:255',
            'certifications.*.issuer' => 'required|string|max:255',
            'certifications.*.date_obtained' => 'required|date',
            'certifications.*.expiration_date' => 'nullable|date|after:certifications.*.date_obtained',
            'languages' => 'nullable|array',
            'languages.*.language' => 'required|string|max:100',
            'languages.*.proficiency' => 'required|string|in:beginner,intermediate,advanced,fluent,native',
            'resume_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'preferences' => 'nullable|array',
        ];

        // For update operations, make fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $optionalRules = [];
            foreach ($rules as $field => $rule) {
                if (!in_array('required', explode('|', $rule))) {
                    $optionalRules[$field] = 'sometimes|' . $rule;
                } else {
                    $optionalRules[$field] = str_replace('required|', '', $rule);
                    $optionalRules[$field] = 'sometimes|' . $optionalRules[$field];
                }
            }
            return $optionalRules;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => 'The email address is already in use.',
            'experience.*.end_date.after' => 'The end date must be after the start date.',
            'education.*.end_date.after' => 'The end date must be after the start date.',
            'certifications.*.expiration_date.after' => 'The expiration date must be after the date obtained.',
        ];
    }
}
