<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScopeReview extends Model
{
    protected $fillable = [
        'entry_date',
        'source',
        'platform',
        'project_name',
        'due_date',
        'project_link',
        'location',
        'notes',
        'reason_to_ignore',
        'bid_stage',
        'assigned_estimator_id',
        'project_type',
        'decision',
        'duration',
        'estimator_notes',
        'uploaded_in_oh',
        'reviewed_at',
        'project_number',
        'allocation_id',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'due_date' => 'date',
        'reviewed_at' => 'datetime',
        'uploaded_in_oh' => 'boolean',
    ];

    public function assignedEstimator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_estimator_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ScopeReviewStatusHistory::class)->orderByDesc('created_at');
    }

    public function isApproved(): bool
    {
        return $this->decision === 'approved';
    }

    public function isConverted(): bool
    {
        return $this->allocation_id !== null;
    }

    /**
     * Whether the due date has already passed (before today). Rows with no due
     * date are not considered past due. Mirrors the readyForAssignment scope's
     * due-date rule.
     */
    public function isPastDue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay());
    }

    /**
     * Whether this row can still be assigned to an allocation: approved, not
     * already converted, and not past its due date.
     */
    public function isAssignable(): bool
    {
        return $this->isApproved() && !$this->isConverted() && !$this->isPastDue();
    }

    public function scopeApproved($query)
    {
        return $query->where('decision', 'approved');
    }

    public function scopeReadyForAssignment($query)
    {
        // Approved, not yet converted to a job, and still actionable by due date
        // (today/future, or no due date). Past-due approvals are historical and
        // can no longer be bid, so they don't belong in the assignment queue.
        return $query->where('decision', 'approved')
            ->whereNull('allocation_id')
            ->where(function ($q) {
                $q->whereNull('due_date')
                  ->orWhereDate('due_date', '>=', now()->toDateString());
            });
    }

    public function scopePendingReview($query)
    {
        return $query->whereNull('decision');
    }
}
