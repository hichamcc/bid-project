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
        "other_gc",
        'scope',
        'assigned_date',
        'due_date',
        'rfi_due_date',
        'rfi_request_date',
        'status',
        'type',
        'rfi',
        'assigned_to',
        'project_information',
        'web_link',
    ];

    protected $casts = [
        'other_gc' => 'array',
        'assigned_date' => 'date',
        'due_date' => 'date',
        'rfi_due_date' => 'date',
        'rfi_request_date' => 'date',
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
     * Get the remarks for this project
     */
    public function remarks(): HasMany
    {
        return $this->hasMany(ProjectRemark::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the proposals for this project
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the progress entries for this project
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class)->orderBy('created_at', 'desc');
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

        
        return now()->diffInDays($this->due_date, false)+1    ;
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
                        $q->whereNotIn('name', ['completed', 'cancelled' , 'DECLINED' , 'CANCELLED','SUBMITTED']);
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
                        $q->whereNotIn('name', ['completed', 'cancelled', 'DECLINED' , 'CANCELLED','SUBMITTED']);
                    });
    }

    public function gcRecord(): BelongsTo
    {
        return $this->belongsTo(Gc::class, 'gc', 'name');
    }
 /**
     * Get the primary GC for this project
     */
    public function primaryGC(): BelongsTo
    {
        return $this->belongsTo(GC::class, 'gc', 'name');
    }

    /**
     * Get all other GCs (excluding primary) as a collection
     */
    public function getOtherGCsAttribute()
    {
        if (empty($this->other_gc)) {
            return collect();
        }

        return GC::whereIn('name', $this->other_gc)->get();
    }

    /**
     * Get all GCs (primary + others) as a collection
     */
    public function getAllGCs()
    {
        $allGCs = collect();
        
        // Add primary GC
        if ($this->primaryGC) {
            $allGCs->push($this->primaryGC);
        }
        
        // Add other GCs
        $allGCs = $allGCs->merge($this->otherGCs);
        
        return $allGCs->unique('id');
    }

    /**
     * Add a GC to the other_gc list
     */
    public function addOtherGC(string $gcName): void
    {
        $otherGCs = $this->other_gc ?? [];
        
        if (!in_array($gcName, $otherGCs) && $gcName !== $this->gc) {
            $otherGCs[] = $gcName;
            $this->other_gc = $otherGCs;
            $this->save();
        }
    }

    /**
     * Remove a GC from the other_gc list
     */
    public function removeOtherGC(string $gcName): void
    {
        $otherGCs = $this->other_gc ?? [];
        $otherGCs = array_filter($otherGCs, fn($gc) => $gc !== $gcName);
        $this->other_gc = array_values($otherGCs); // Re-index array
        $this->save();
    }

    /**
     * Set multiple other GCs at once
     */
    public function setOtherGCs(array $gcNames): void
    {
        // Filter out the primary GC to avoid duplication
        $filtered = array_filter($gcNames, fn($name) => $name !== $this->gc);
        $this->other_gc = array_values(array_unique($filtered));
        $this->save();
    }

    /**
     * Check if a GC is associated with this project (primary or other)
     */
    public function hasGC(string $gcName): bool
    {
        if ($this->gc === $gcName) {
            return true;
        }

        return in_array($gcName, $this->other_gc ?? []);
    }

    /**
     * Get count of all associated GCs
     */
    public function getTotalGCsCountAttribute(): int
    {
        $count = $this->gc ? 1 : 0; // Primary GC
        $count += count($this->other_gc ?? []); // Other GCs
        return $count;
    }

    /**
     * Scope to filter projects by any associated GC (primary or other)
     */
    public function scopeForGC($query, string $gcName)
    {
        return $query->where('gc', $gcName)
                    ->orWhere('other_gc', 'LIKE', '%"' . $gcName . '"%');
    }

    /**
     * Get formatted list of all GC names
     */
    public function getFormattedGCsAttribute(): string
    {
        $gcs = [];
        
        if ($this->gc) {
            $gcs[] = $this->gc . ' (Primary)';
        }
        
        if (!empty($this->other_gc)) {
            foreach ($this->other_gc as $gc) {
                $gcs[] = $gc;
            }
        }
        
        return implode(', ', $gcs);
    }

    /**
     * Get days until RFI due date
     */
    public function daysUntilRFI(): ?int
    {
        if (!$this->rfi_due_date) {
            return null;
        }

        return now()->diffInDays($this->rfi_due_date, false)+1;
    }

    /**
     * Get days until RFI request date
     */
    public function daysUntilRFIRequest(): ?int
    {
        if (!$this->rfi_request_date) {
            return null;
        }

        return now()->diffInDays($this->rfi_request_date, false)+1;
    }
}