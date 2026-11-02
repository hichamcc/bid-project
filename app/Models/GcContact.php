<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcContact extends Model
{
    protected $fillable = [
        'gc_id',
        'first_name',
        'last_name',
        'role',
        'office_number',
        'mobile',
        'email',
    ];

    public function gc(): BelongsTo
    {
        return $this->belongsTo(Gc::class, 'gc_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
