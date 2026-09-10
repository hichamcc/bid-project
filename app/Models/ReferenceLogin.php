<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceLogin extends Model
{
    protected $fillable = [
        'platform',
        'email',
        'password',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        // Encrypted at rest — decrypted transparently on access, admin-only UI.
        'password'   => 'encrypted',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('platform');
    }
}
