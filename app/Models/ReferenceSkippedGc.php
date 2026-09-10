<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSkippedGc extends Model
{
    protected $table = 'reference_skipped_gcs';

    protected $fillable = [
        'gc_name',
        'status',
        'note',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public const STATUSES = ['SKIP', 'PROCEED', 'ON HOLD', 'NO MU PROJECTS'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('gc_name');
    }

    /**
     * Tailwind badge classes per status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'PROCEED'        => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'ON HOLD'        => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'NO MU PROJECTS' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default          => 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200', // SKIP
        };
    }
}
