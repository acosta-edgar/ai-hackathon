<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchCriteria extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_profile_id',
        'name',
        'is_default',
        'keywords',
        'locations',
        'job_type',
        'experience_level',
        'min_salary',
        'max_salary',
        'salary_currency',
        'is_remote',
        'industries',
        'companies',
        'job_titles',
        'skills_included',
        'skills_excluded',
        'days_posted',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'keywords' => 'array',
        'locations' => 'array',
        'industries' => 'array',
        'companies' => 'array',
        'job_titles' => 'array',
        'skills_included' => 'array',
        'skills_excluded' => 'array',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_remote' => 'boolean',
        'days_posted' => 'integer',
        'is_active' => 'boolean',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }
}
