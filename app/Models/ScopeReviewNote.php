<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeReviewNote extends Model
{
    protected $fillable = [
        'scope_review_id',
        'user_id',
        'context',
        'body',
    ];

    public function scopeReview(): BelongsTo
    {
        return $this->belongsTo(ScopeReview::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the given user may edit or delete this note: the author, or an admin.
     */
    public function editableBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $this->user_id === $user->id;
    }
}
