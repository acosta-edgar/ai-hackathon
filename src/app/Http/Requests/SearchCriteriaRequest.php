<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SearchCriteriaRequest extends ApiRequest
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
            'name' => 'required|string|max:255',
            'is_default' => 'boolean',
            'keywords' => 'required_without_all:job_titles,companies,skills_included|nullable|array|min:1',
            'keywords.*' => 'string|max:100',
            'locations' => 'nullable|array',
            'locations.*' => 'string|max:255',
            'job_type' => 'nullable|string|in:full-time,part-time,contract,temporary,internship,volunteer,per-diem',
            'experience_level' => 'nullable|string|in:internship,entry,associate,mid-senior,senior,director,executive',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gt:min_salary',
            'salary_currency' => 'required_with:min_salary,max_salary|string|size:3',
            'is_remote' => 'boolean',
            'industries' => 'nullable|array',
            'industries.*' => 'string|max:100',
            'companies' => 'nullable|array',
            'companies.*' => 'string|max:255',
            'job_titles' => 'nullable|array',
            'job_titles.*' => 'string|max:255',
            'skills_included' => 'nullable|array',
            'skills_included.*' => 'string|max:100',
            'skills_excluded' => 'nullable|array',
            'skills_excluded.*' => 'string|max:100',
            'days_posted' => 'nullable|integer|min:1|max:90',
            'is_active' => 'boolean',
        ];

        // For update operations, make fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $optionalRules = [];
            foreach ($rules as $field => $rule) {
                if ($field === 'user_profile_id') {
                    $optionalRules[$field] = 'sometimes|' . $rule;
                } elseif (!in_array('required', explode('|', $rule)) && !str_contains($rule, 'required_with')) {
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
            if ($this->has('min_salary') && $this->has('max_salary') && 
                $this->min_salary >= $this->max_salary) {
                $validator->errors()->add('max_salary', 'Maximum salary must be greater than minimum salary.');
            }

            if ($this->has('is_default') && $this->is_default === true) {
                $count = $this->user_profile_id ? 
                    \App\Models\SearchCriteria::where('user_profile_id', $this->user_profile_id)
                        ->where('is_default', true)
                        ->where('id', '!=', $this->route('search_criteria'))
                        ->count() : 0;

                if ($count > 0) {
                    $validator->errors()->add('is_default', 'Only one search criteria can be set as default.');
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
            'keywords.required_without_all' => 'At least one of keywords, job titles, companies, or skills must be provided.',
            'salary_currency.required_with' => 'Currency is required when specifying a salary range.',
            'max_salary.gt' => 'Maximum salary must be greater than minimum salary.',
            'days_posted.max' => 'Cannot search for jobs posted more than 90 days ago.',
        ];
    }
}
