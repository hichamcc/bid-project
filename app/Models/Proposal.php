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
        'job_number',
        'submission_date',
        'responded',
        'first_follow_up_date',
        'first_follow_up_respond',
        'first_follow_up_attachment',
        'second_follow_up_date',
        'second_follow_up_respond',
        'second_follow_up_attachment',
        'third_follow_up_date',
        'third_follow_up_respond',
        'third_follow_up_attachment',
        'price_original',
        'price_ve',
        'result_gc',
        'result_art',
        'gc_price',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'first_follow_up_date' => 'date',
        'second_follow_up_date' => 'date',
        'third_follow_up_date' => 'date',
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
     * Get GC result badge color
     */
    public function getGcResultColor(): string
    {
        return match($this->result_gc) {
            'win' => 'green',
            'loss' => 'red',
            default => 'yellow'
        };
    }

    /**
     * Get ART result badge color  
     */
    public function getArtResultColor(): string
    {
        return match($this->result_art) {
            'win' => 'green',
            'loss' => 'red',
            default => 'yellow'
        };
    }

    /**
     * Get formatted GC result
     */
    public function getFormattedGcResult(): string
    {
        return $this->result_gc ? ucfirst($this->result_gc) : 'Pending';
    }

    /**
     * Get formatted ART result
     */
    public function getFormattedArtResult(): string
    {
        return $this->result_art ? ucfirst($this->result_art) : 'Pending';
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
     * Scope for GC winning proposals
     */
    public function scopeGcWins($query)
    {
        return $query->where('result_gc', 'win');
    }

    /**
     * Scope for GC losing proposals
     */
    public function scopeGcLosses($query)
    {
        return $query->where('result_gc', 'loss');
    }

    /**
     * Scope for ART winning proposals
     */
    public function scopeArtWins($query)
    {
        return $query->where('result_art', 'win');
    }

    /**
     * Scope for ART losing proposals
     */
    public function scopeArtLosses($query)
    {
        return $query->where('result_art', 'loss');
    }

    /**
     * Scope for proposals with GC results
     */
    public function scopeWithGcResults($query)
    {
        return $query->whereNotNull('result_gc');
    }

    /**
     * Scope for proposals with ART results
     */
    public function scopeWithArtResults($query)
    {
        return $query->whereNotNull('result_art');
    }

    /**
     * Get formatted response status
     */
    public function getFormattedResponse(string $field): string
    {
        return $this->$field ? ucfirst($this->$field) : '-';
    }
}