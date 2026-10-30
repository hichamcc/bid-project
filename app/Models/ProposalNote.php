<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalNote extends Model
{
    protected $fillable = [
        'proposal_id',
        'user_id',
        'body',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the given user may delete this note: the author, or an admin.
     */
    public function editableBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $this->user_id === $user->id;
    }
}
