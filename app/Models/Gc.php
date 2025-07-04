<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class GC extends Model
{
    use HasFactory;

    protected $table = 'gcs';

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];



    // update the projects with the new name
/**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // Listen for the 'updating' event
        static::updating(function ($gc) {
            // Check if the name is being changed
            if ($gc->isDirty('name')) {
                $oldName = $gc->getOriginal('name');
                $newName = $gc->name;
                
                // Update all projects where this GC is the primary GC
                Project::where('gc', $oldName)->update(['gc' => $newName]);
                
                // Update all projects where this GC appears in other_gc array
                $projectsWithOtherGC = Project::where('other_gc', 'LIKE', '%"' . $oldName . '"%')->get();
                
                foreach ($projectsWithOtherGC as $project) {
                    if ($project->other_gc && is_array($project->other_gc)) {
                        // Replace old name with new name in the array
                        $otherGCs = $project->other_gc;
                        $key = array_search($oldName, $otherGCs);
                        
                        if ($key !== false) {
                            $otherGCs[$key] = $newName;
                            $project->other_gc = $otherGCs;
                            $project->save();
                        }
                    }
                }
            }
        });
    }

/**
 * Get all projects associated with this GC (as primary or other GC)
 */
public function projects()
{
    return $this->hasMany(Project::class, 'gc', 'name')
                ->orWhere('other_gc', 'LIKE', '%"' . $this->name . '"%');
}

/**
 * Get projects where this GC is the primary GC
 */
public function primaryProjects(): HasMany
{
    return $this->hasMany(Project::class, 'gc', 'name');
}

/**
 * Get projects where this GC appears in the other_gc array
 */
public function otherProjects()
{
    // user contains instead of where and like keep hasMany
    return $this->hasMany(Project::class, 'other_gc', 'name')
                ->where('other_gc', 'LIKE', '%"' . $this->name . '"%');
   
}


/**
 * Get all projects count (primary + other)
 */
public function getProjectsCountAttribute(): int
{
    return $this->projects()->count();
}

/**
 * Get active projects count (primary + other)
 */
public function getActiveProjectsCountAttribute(): int
{
    return $this->projects()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();
}


    /**
     * Scope for active GCs only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered GCs
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get the GC's initials for avatar display
     */
    public function initials(): string
    {
        $words = explode(' ', trim($this->name));
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $this->phone);

        // Format as (XXX) XXX-XXXX if it's a 10-digit US number
        if (strlen($phone) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 4)
            );
        }

        return $this->phone; // Return original if not standard format
    }

    /**
     * Get the display name (company if available, otherwise name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company ?: $this->name;
    }

    /**
     * Check if GC has any projects
     */
    public function hasProjects(): bool
    {
        return $this->projects()->exists();
    }


    /**
     * Check if GC can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasProjects();
    }
}