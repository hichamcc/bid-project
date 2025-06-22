<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRemark extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'remark',
    ];

    /**
     * Get the project this remark belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created this remark
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}