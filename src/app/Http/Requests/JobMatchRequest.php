<?php

namespace App\Http\Requests;

use App\Models\JobMatch;
use Illuminate\Validation\Rule;

class JobMatchRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'user_profile_id' => 'required|exists:user_profiles,id',
            'job_id' => 'required|exists:jobs,id',
            'search_criteria_id' => 'nullable|exists:search_criteria,id',
            'overall_score' => 'required|integer|min:0|max:100',
            'skills_score' => 'nullable|integer|min:0|max:100',
            'experience_score' => 'nullable|integer|min:0|max:100',
            'education_score' => 'nullable|integer|min:0|max:100',
            'company_fit_score' => 'nullable|integer|min:0|max:100',
            'strengths' => 'nullable|array',
            'strengths.*' => 'string|max:255',
            'weaknesses' => 'nullable|array',
            'weaknesses.*' => 'string|max:255',
            'missing_skills' => 'nullable|array',
            'missing_skills.*' => 'string|max:100',
            'matching_skills' => 'nullable|array',
            'matching_skills.*' => 'string|max:100',
            'match_summary' => 'nullable|string',
            'improvement_suggestions' => 'nullable|string',
            'application_advice' => 'nullable|string',
            'is_interested' => 'boolean',
            'is_not_interested' => 'boolean',
            'user_notes' => 'nullable|string',
            'status' => [
                'nullable',
                Rule::in([
                    JobMatch::STATUS_NEW,
                    JobMatch::STATUS_VIEWED,
                    JobMatch::STATUS_APPLIED,
                    JobMatch::STATUS_INTERVIEW,
                    JobMatch::STATUS_OFFER,
                    JobMatch::STATUS_REJECTED,
                    JobMatch::STATUS_CLOSED,
                ]),
            ],
            'status_history' => 'nullable|array',
            'status_history.*.status' => [
                'required',
                Rule::in([
                    JobMatch::STATUS_NEW,
                    JobMatch::STATUS_VIEWED,
                    JobMatch::STATUS_APPLIED,
                    JobMatch::STATUS_INTERVIEW,
                    JobMatch::STATUS_OFFER,
                    JobMatch::STATUS_REJECTED,
                    JobMatch::STATUS_CLOSED,
                ]),
            ],
            'status_history.*.changed_at' => 'required|date',
            'viewed_at' => 'nullable|date',
            'applied_at' => 'nullable|date',
            'rejected_at' => 'nullable|date',
        ];

        // For update operations, make fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $optionalRules = [];
            foreach ($rules as $field => $rule) {
                if ($field === 'user_profile_id' || $field === 'job_id') {
                    $optionalRules[$field] = 'sometimes|' . $rule;
                } elseif (!in_array('required', explode('|', $rule))) {
                    $optionalRules[$field] = 'sometimes|' . $rule;
                } else {
                    $optionalRules[$field] = str_replace('required|', 'sometimes|required|', $rule);
                }
            }
            return $optionalRules;
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure at most one of is_interested or is_not_interested is true
            if ($this->is_interested && $this->is_not_interested) {
                $validator->errors()->add('is_interested', 'A job cannot be both interested and not interested.');
                $validator->errors()->add('is_not_interested', 'A job cannot be both interested and not interested.');
            }

            // Validate status_history format if provided
            if ($this->has('status_history') && is_array($this->status_history)) {
                foreach ($this->status_history as $index => $entry) {
                    if (!isset($entry['status']) || !isset($entry['changed_at'])) {
                        $validator->errors()->add(
                            "status_history.{$index}",
                            'Each status history entry must have both status and changed_at fields.'
                        );
                    }
                }
            }

            // Check for duplicate job match
            if ($this->isMethod('POST')) {
                $exists = \App\Models\JobMatch::where('user_profile_id', $this->user_profile_id)
                    ->where('job_id', $this->job_id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('job_id', 'A job match already exists for this user and job.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'overall_score.between' => 'The overall score must be between 0 and 100.',
            'status.in' => 'The selected status is invalid.',
            'status_history.*.status.in' => 'The selected status in history is invalid.',
            'status_history.*.changed_at.required' => 'The changed at field is required for status history.',
        ];
    }
}
