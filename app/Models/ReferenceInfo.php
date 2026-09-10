<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceInfo extends Model
{
    protected $fillable = [
        'item',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('item');
    }
}
