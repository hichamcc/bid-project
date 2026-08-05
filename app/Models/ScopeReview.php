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

    public function scopeApproved($query)
    {
        return $query->where('decision', 'approved');
    }

    public function scopeReadyForAssignment($query)
    {
        return $query->where('decision', 'approved')->whereNull('allocation_id');
    }

    public function scopePendingReview($query)
    {
        return $query->whereNull('decision');
    }
}
