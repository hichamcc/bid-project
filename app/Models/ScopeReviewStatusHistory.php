<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeReviewStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'scope_review_id',
        'user_id',
        'decision',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function scopeReview(): BelongsTo
    {
        return $this->belongsTo(ScopeReview::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
