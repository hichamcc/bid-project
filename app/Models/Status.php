<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];


    // update the projects with the new name only for status
    protected static function booted()
    {
        static::updating(function ($status) {
            if ($status->isDirty('name')) {
                $oldName = $status->getOriginal('name');
                $newName = $status->name;   

                // Update all projects where this status is the primary status
                Project::where('status', $oldName)->update(['status' => $newName]);
            }
        });
    }

    /**
     * Get all projects with this status
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'status', 'name');
    }

    /**
     * Scope for active statuses only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered statuses
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the Tailwind CSS color class based on hex color
     */

     //correct the color class to handle all colors range from 000000 to ffffff

    public function getTailwindColorClass(): string
    {
        return match($this->color) {        
            '#ef4444' => 'red',
            '#f97316' => 'orange', 
            '#eab308' => 'yellow',
            '#22c55e' => 'green',
            '#3b82f6' => 'blue',
            '#8b5cf6' => 'purple',
            '#ec4899' => 'pink',
            '#6b7280' => 'gray',
            default => 'gray'
        };
    }

    public function getStatusColor()
    {
        return $this->getTailwindColorClass();
    }
}