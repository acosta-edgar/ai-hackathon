<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_profile_id',
        'job_id',
        'search_criteria_id',
        'overall_score',
        'skills_score',
        'experience_score',
        'education_score',
        'company_fit_score',
        'strengths',
        'weaknesses',
        'missing_skills',
        'matching_skills',
        'match_summary',
        'improvement_suggestions',
        'application_advice',
        'is_interested',
        'is_not_interested',
        'user_notes',
        'viewed_at',
        'applied_at',
        'rejected_at',
        'status',
        'status_history',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'skills_score' => 'integer',
        'experience_score' => 'integer',
        'education_score' => 'integer',
        'company_fit_score' => 'integer',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'missing_skills' => 'array',
        'matching_skills' => 'array',
        'is_interested' => 'boolean',
        'is_not_interested' => 'boolean',
        'viewed_at' => 'datetime',
        'applied_at' => 'datetime',
        'rejected_at' => 'datetime',
        'status_history' => 'array',
    ];

    // Status constants
    public const STATUS_NEW = 'new';
    public const STATUS_VIEWED = 'viewed';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_INTERVIEW = 'interview';
    public const STATUS_OFFER = 'offer';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function searchCriteria(): BelongsTo
    {
        return $this->belongsTo(SearchCriteria::class);
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update([
                'viewed_at' => now(),
                'status' => self::STATUS_VIEWED,
                'status_history' => array_merge(
                    $this->status_history ?? [],
                    [
                        [
                            'status' => self::STATUS_VIEWED,
                            'changed_at' => now()->toDateTimeString(),
                        ]
                    ]
                )
            ]);
        }
    }

    public function markAsApplied(): void
    {
        $this->update([
            'applied_at' => now(),
            'status' => self::STATUS_APPLIED,
            'status_history' => array_merge(
                $this->status_history ?? [],
                [
                    [
                        'status' => self::STATUS_APPLIED,
                        'changed_at' => now()->toDateTimeString(),
                    ]
                ]
            )
        ]);
    }

    public function markAsRejected(): void
    {
        $this->update([
            'rejected_at' => now(),
            'status' => self::STATUS_REJECTED,
            'status_history' => array_merge(
                $this->status_history ?? [],
                [
                    [
                        'status' => self::STATUS_REJECTED,
                        'changed_at' => now()->toDateTimeString(),
                    ]
                ]
            )
        ]);
    }
}
