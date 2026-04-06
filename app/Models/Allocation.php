<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allocation extends Model
{
    protected $fillable = [
        'job_number',
        'due_date',
        'assigned_date',
        'days_required',
        'job_type',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assigned_date' => 'date',
    ];

    public function estimators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'allocation_user')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getProjectNameAttribute(): ?string
    {
        $name = $this->projects()->value('name');
        if (!$name) return null;
        // Strip leading number+letter+dot prefix (e.g. "1111A. " or "(NC) 26077. ") leaving just the name
        return trim(preg_replace('/^[^0-9]*[0-9]+[A-Za-z]*\.\s*/', '', $name)) ?: null;
    }
}
