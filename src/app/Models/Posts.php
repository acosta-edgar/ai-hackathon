<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posts extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_board_id',
        'external_id',
        'title',
        'description',
        'company_name',
        'company_website',
        'location',
        'is_remote',
        'post_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'skills',
        'categories',
        'apply_url',
        'post_url',
        'posted_at',
        'expires_at',
        'is_active',
        'raw_data',
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'skills' => 'array',
        'categories' => 'array',
        'posted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'raw_data' => 'array',
    ];

    public function postBoard(): BelongsTo
    {
        return $this->belongsTo(PostBoard::class);
    }

    public function postMatches(): HasMany
    {
        return $this->hasMany(PostMatch::class);
    }

    public function getSalaryRangeAttribute(): ?string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        $range = [];
        if ($this->salary_min) {
            $range[] = number_format($this->salary_min, 2);
        }
        if ($this->salary_max) {
            $range[] = number_format($this->salary_max, 2);
        }

        $salary = implode(' - ', $range);
        $currency = $this->salary_currency ?? 'USD';
        $period = $this->salary_period ? "/{$this->salary_period}" : '';

        return "{$currency} {$salary}{$period}";
    }
}
