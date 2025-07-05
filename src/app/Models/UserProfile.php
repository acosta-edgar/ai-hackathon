<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'title',
        'summary',
        'skills',
        'experience',
        'education',
        'certifications',
        'languages',
        'resume_url',
        'linkedin_url',
        'github_url',
        'website_url',
        'is_active',
        'preferences',
    ];

    protected $casts = [
        'skills' => 'array',
        'experience' => 'array',
        'education' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'preferences' => 'array',
        'is_active' => 'boolean',
    ];

    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }

    public function searchCriteria(): HasMany
    {
        return $this->hasMany(SearchCriteria::class);
    }
}
