<?php

namespace App\Console\Commands;

use App\Models\GcEmail;
use App\Models\Proposal;
use Illuminate\Console\Command;

class IndexProposalEmails extends Command
{
    protected $signature = 'proposals:index-emails {--fresh : Re-read files that were already indexed}';

    protected $description = 'Read the follow-up email attachments of all proposals into gc_emails (GC page > Emails tab)';

    public function handle(): int
    {
        $force = (bool) $this->option('fresh');
        $total = Proposal::count();
        $bar = $this->output->createProgressBar($total);

        Proposal::query()->orderBy('id')->chunkById(200, function ($proposals) use ($force, $bar) {
            foreach ($proposals as $proposal) {
                GcEmail::syncProposal($proposal, $force);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $rows = GcEmail::where('source', 'upload')
            ->selectRaw('file_type, COUNT(*) as files, SUM(parse_error IS NULL AND subject IS NOT NULL) as read_ok, SUM(parse_error IS NOT NULL) as failed')
            ->groupBy('file_type')
            ->get()
            ->map(fn ($r) => [$r->file_type ?: '?', $r->files, (int) $r->read_ok, (int) $r->failed])
            ->all();

        $this->table(['Type', 'Files', 'Email details read', 'Failed'], $rows);

        $errors = GcEmail::whereNotNull('parse_error')->limit(10)->pluck('parse_error', 'file_path');
        foreach ($errors as $path => $error) {
            $this->warn("{$path}: {$error}");
        }

        return self::SUCCESS;
    }
}
