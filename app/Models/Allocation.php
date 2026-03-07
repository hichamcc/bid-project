<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allocation extends Model
{
    protected $fillable = [
        'job_number',
        'due_date',
        'assigned_date',
        'days_required',
        'job_type',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assigned_date' => 'date',
    ];

    public function estimators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'allocation_user');
    }
}
