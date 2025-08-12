<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progress';

    protected $fillable = [
        'job_number',
        'project_id',
        'assigned_date',
        'submission_date',
        'total_sqft',
        'total_lnft',
        'total_sinks',
        'total_slabs',
        'total_hours',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'submission_date' => 'date',
        'total_sqft' => 'decimal:2',
        'total_lnft' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'total_sinks' => 'integer',
        'total_slabs' => 'integer',
    ];

    /**
     * Get the project that this progress belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get formatted total square feet
     */
    public function getFormattedSqftAttribute(): string
    {
        return $this->total_sqft ? number_format($this->total_sqft, 2) . ' sq ft' : '-';
    }

    /**
     * Get formatted total linear feet
     */
    public function getFormattedLnftAttribute(): string
    {
        return $this->total_lnft ? number_format($this->total_lnft, 2) . ' ln ft' : '-';
    }

    /**
     * Get formatted total hours
     */
    public function getFormattedHoursAttribute(): string
    {
        return $this->total_hours ? number_format($this->total_hours, 2) . ' hrs' : '-';
    }

    /**
     * Calculate progress completion percentage
     */
    public function getCompletionPercentage(): ?float
    {
        if (!$this->assigned_date) {
            return null;
        }

        if ($this->submission_date) {
            return 100;
        }

        // Calculate based on days elapsed vs estimated duration
        $daysElapsed = $this->assigned_date->diffInDays(now());
        $estimatedDays = 30; // Default estimation, can be made configurable

        return min(($daysElapsed / $estimatedDays) * 100, 99);
    }

    /**
     * Check if progress is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->assigned_date || $this->submission_date) {
            return false;
        }

        $estimatedDays = 30; // Default estimation
        return $this->assigned_date->addDays($estimatedDays)->isPast();
    }

    /**
     * Get the assigned estimator through the project relationship
     */
    public function getEstimatorAttribute()
    {
        return $this->project?->assignedTo;
    }

    /**
     * Scope for progress entries by estimator
     */
    public function scopeByEstimator($query, $estimatorId)
    {
        return $query->whereHas('project', function($q) use ($estimatorId) {
            $q->where('assigned_to', $estimatorId);
        });
    }

    /**
     * Scope for completed progress entries
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('submission_date');
    }

    /**
     * Scope for pending progress entries
     */
    public function scopePending($query)
    {
        return $query->whereNull('submission_date')->whereNotNull('assigned_date');
    }

    /**
     * Scope for overdue progress entries
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('submission_date')
                    ->whereNotNull('assigned_date')
                    ->where('assigned_date', '<=', now()->subDays(30));
    }
}