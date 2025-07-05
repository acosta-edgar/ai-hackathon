<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostBoard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'type',
        'description',
        'requires_authentication',
        'authentication_details',
        'search_parameters',
        'is_active',
        'search_frequency_hours',
        'last_searched_at',
    ];

    protected $casts = [
        'authentication_details' => 'array',
        'search_parameters' => 'array',
        'is_active' => 'boolean',
        'requires_authentication' => 'boolean',
        'search_frequency_hours' => 'integer',
        'last_searched_at' => 'datetime',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
