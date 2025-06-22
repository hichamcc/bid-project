<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'gc',
        'scope',
        'assigned_date',
        'due_date',
        'status',
        'type',
        'rfi',
        'assigned_to',
        'project_information',
        'web_link',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the type record for this project
     */
    public function typeRecord(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type', 'name');
    }

    /**
     * Get the status record for this project
     */
    public function statusRecord(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status', 'name');
    }
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user assigned to this project
     */
    public function remarks(): HasMany
    {
        return $this->hasMany(ProjectRemark::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || !$this->statusRecord) {
            return false;
        }

        // You can define which statuses are considered "not completed"
        $nonCompletedStatuses = ['pending', 'in_progress', 'under_review', 'on_hold'];
        
        return $this->due_date->isPast() && 
               in_array(strtolower($this->status), $nonCompletedStatuses);
    }

    /**
     * Get type badge color from type record
     */
    public function getTypeColor(): string
    {
        return $this->typeRecord?->getTailwindColorClass() ?? 'gray';
    }

    /**
     * Get formatted type
     */
    public function getFormattedType(): string
    {
        return $this->type ?? 'No Type';
    }

    /**
     * Get status badge color from status record
     */
    public function getStatusColor(): string
    {
        return $this->statusRecord?->getTailwindColorClass() ?? 'gray';
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatus(): string
    {
        return $this->status ?? 'No Status';
    }

    /**
     * Get days until due date
     */
    public function daysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by assigned user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for overdue projects
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
                    ->whereHas('statusRecord', function($q) {
                        // Assuming you have status names like these
                        $q->whereNotIn('name', ['completed', 'cancelled']);
                    });
    }

    /**
     * Scope for due soon (within 7 days)
     */
    public function scopeDueSoon($query)
    {
        return $query->whereDate('due_date', '>=', now())
                    ->whereDate('due_date', '<=', now()->addDays(7))
                    ->whereHas('statusRecord', function($q) {
                        // Assuming you have status names like these
                        $q->whereNotIn('name', ['completed', 'cancelled']);
                    });
    }
}