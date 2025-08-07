<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'submission_date',
        'price_original',
        'price_ve',
        'result',
        'gc_price',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'price_original' => 'decimal:2',
        'price_ve' => 'decimal:2', 
        'gc_price' => 'decimal:2',
    ];

    /**
     * Get the project that owns the proposal
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get result badge color
     */
    public function getResultColor(): string
    {
        return match($this->result) {
            'win' => 'green',
            'loss' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get formatted result
     */
    public function getFormattedResult(): string
    {
        return $this->result ? ucfirst($this->result) : 'Pending';
    }

    /**
     * Calculate VE savings amount
     */
    public function getVESavings(): ?float
    {
        if ($this->price_original && $this->price_ve) {
            return $this->price_original - $this->price_ve;
        }
        return null;
    }

    /**
     * Calculate VE savings percentage
     */
    public function getVESavingsPercentage(): ?float
    {
        if ($this->price_original && $this->price_ve && $this->price_original > 0) {
            return (($this->price_original - $this->price_ve) / $this->price_original) * 100;
        }
        return null;
    }

    /**
     * Scope for winning proposals
     */
    public function scopeWins($query)
    {
        return $query->where('result', 'win');
    }

    /**
     * Scope for losing proposals
     */
    public function scopeLosses($query)
    {
        return $query->where('result', 'loss');
    }

    /**
     * Scope for proposals with results
     */
    public function scopeWithResults($query)
    {
        return $query->whereNotNull('result');
    }
}