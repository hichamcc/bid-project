<?php

namespace App\Models;

use App\Services\EmailFileParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class GcEmail extends Model
{
    /** follow_up number => proposal column prefix */
    public const FOLLOW_UPS = [
        1 => 'first',
        2 => 'second',
        3 => 'third',
        4 => 'fourth',
    ];

    protected $fillable = [
        'gc_id',
        'proposal_id',
        'follow_up',
        'source',
        'file_path',
        'file_type',
        'sent_at',
        'from_name',
        'from_email',
        'to_name',
        'to_email',
        'subject',
        'body_preview',
        'attachments',
        'parse_error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function gc(): BelongsTo
    {
        return $this->belongsTo(Gc::class, 'gc_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Emails linked to a GC directly (future Outlook sync) or through the
     * proposals of projects where it is the primary GC.
     */
    public function scopeForGc(Builder $query, $gc): Builder
    {
        return $query->where(function ($q) use ($gc) {
            $q->where('gc_id', $gc->id)
              ->orWhereHas('proposal.project', fn ($p) => $p->where('gc', $gc->name));
        });
    }

    /**
     * Date shown in "Sent On": the email's own date, or the follow-up date for
     * files that have none (pdf / screenshots).
     */
    public function getDisplayDateAttribute(): ?Carbon
    {
        if ($this->sent_at) {
            return $this->sent_at;
        }

        $prefix = self::FOLLOW_UPS[$this->follow_up] ?? null;

        return $prefix && $this->proposal ? $this->proposal->{"{$prefix}_follow_up_date"} : null;
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    /**
     * Keep the email rows of a proposal in line with its follow-up attachments.
     * Files that didn't change are skipped unless $force is true.
     */
    public static function syncProposal(Proposal $proposal, bool $force = false): void
    {
        $existing = static::where('proposal_id', $proposal->id)
            ->whereNotNull('follow_up')
            ->get()
            ->keyBy('follow_up');

        foreach (self::FOLLOW_UPS as $number => $prefix) {
            $path = $proposal->{"{$prefix}_follow_up_attachment"};
            $row = $existing->get($number);

            if (!$path) {
                $row?->delete();
                continue;
            }

            if ($row && $row->file_path === $path && !$force) {
                continue;
            }

            $empty = array_fill_keys([
                'sent_at', 'from_name', 'from_email', 'to_name', 'to_email',
                'subject', 'body_preview', 'attachments', 'parse_error',
            ], null);

            static::updateOrCreate(
                ['proposal_id' => $proposal->id, 'follow_up' => $number],
                array_merge($empty, EmailFileParser::parseStoredFile($path), [
                    'source' => 'upload',
                    'file_path' => $path,
                ])
            );
        }
    }
}
