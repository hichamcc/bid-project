<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimatorOffDay extends Model
{
    protected $fillable = ['user_id', 'start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function estimator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
