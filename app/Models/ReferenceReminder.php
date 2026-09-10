<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceReminder extends Model
{
    protected $fillable = [
        'user_id',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The author, or any admin, may edit/delete a reminder.
     */
    public function editableBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $this->user_id === $user->id;
    }
}
