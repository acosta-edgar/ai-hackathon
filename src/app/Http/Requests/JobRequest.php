<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PostRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'post_board_id' => 'required|exists:post_boards,id',
            'external_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_logo_url' => 'nullable|url|max:255',
            'location' => 'required|string|max:255',
            'is_remote' => 'boolean',
            'post_type' => 'nullable|string|in:full-time,part-time,contract,temporary,internship,volunteer,per-diem',
            'experience_level' => 'nullable|string|in:internship,entry,associate,mid-senior,senior,director,executive',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gt:salary_min',
            'salary_currency' => 'required_with:salary_min,salary_max|string|size:3',
            'salary_period' => 'nullable|string|in:hour,day,week,month,year',
            'salary_is_estimate' => 'boolean',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'apply_url' => 'required|url|max:255',
            'post_url' => 'required|url|max:255',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:posted_at',
            'is_active' => 'boolean',
            'raw_data' => 'nullable|array',
        ];

        // For update operations, make fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $optionalRules = [];
            foreach ($rules as $field => $rule) {
                if ($field === 'post_board_id' || $field === 'external_id') {
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
            if ($this->has('salary_min') && $this->has('salary_max') && 
                $this->salary_min >= $this->salary_max) {
                $validator->errors()->add('salary_max', 'Maximum salary must be greater than minimum salary.');
            }

            // Check for duplicate external_id for the same post board
            if ($this->isMethod('POST') || $this->isMethod('PUT') || $this->isMethod('PATCH')) {
                $query = \App\Models\Post::where('post_board_id', $this->post_board_id)
                    ->where('external_id', $this->external_id);

                if ($this->route('post')) {
                    $query->where('id', '!=', $this->route('post'));
                }

                if ($query->exists()) {
                    $validator->errors()->add('external_id', 'A post with this external ID already exists for the specified post board.');
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
            'external_id.unique' => 'A post with this external ID already exists for the specified post board.',
            'salary_max.gt' => 'Maximum salary must be greater than minimum salary.',
            'expires_at.after' => 'Expiration date must be after the posted date.',
        ];
    }
}
