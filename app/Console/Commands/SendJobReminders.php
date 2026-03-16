<?php

namespace App\Console\Commands;

use App\Mail\JobDueTodayMail;
use App\Mail\JobOverdueMail;
use App\Models\Allocation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendJobReminders extends Command
{
    protected $signature   = 'jobs:reminders {type : due-today or overdue}';
    protected $description = 'Send job reminder emails to estimators (due-today) or overdue alerts to admins (overdue)';

    private const OVERDUE_RECIPIENTS = [
        'commercial.admin@artelye.com',
        'reyhan@urbansourcecountertops.com',
    ];

    public function handle(): void
    {
        match ($this->argument('type')) {
            'due-today' => $this->sendDueTodayReminders(),
            'overdue'   => $this->sendOverdueAlerts(),
            default     => $this->error('Unknown type. Use: due-today or overdue'),
        };
    }

    private function sendDueTodayReminders(): void
    {
        $today = Carbon::now('America/New_York')->toDateString();

        $jobs = Allocation::with('estimators')
            ->whereDate('assigned_date', $today)
            ->whereHas('estimators', fn($q) => $q->where('allocation_user.status', 'open'))
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No jobs due today — no reminders sent.');
            return;
        }

        $count = 0;
        foreach ($jobs as $job) {
            foreach ($job->estimators as $estimator) {
                // Only remind estimators whose own status is still open
                if ($estimator->pivot->status !== 'open') {
                    continue;
                }
                if ($count > 0) {
                    usleep(600000); // 600ms between sends — Resend allows max 2 req/sec
                }
                Mail::to($estimator->email)->send(new JobDueTodayMail($job, $estimator));
                $count++;
            }
        }

        $this->info("Due-today reminders sent for {$count} estimator(s).");
    }

    private function sendOverdueAlerts(): void
    {
        $today = Carbon::now('America/New_York')->toDateString();

        // Fire the day after assigned_date (estimator's due date + 1 day)
        $yesterday = Carbon::now('America/New_York')->subDay()->toDateString();

        $overdueJobs = Allocation::with('estimators')
            ->whereDate('assigned_date', $yesterday)
            ->whereHas('estimators', fn($q) => $q->where('allocation_user.status', 'open'))
            ->get();

        if ($overdueJobs->isEmpty()) {
            $this->info('No overdue jobs — no alert sent.');
            return;
        }

        // Build per-estimator overdue list (only their open jobs)
        $estimatorJobs = [];
        foreach ($overdueJobs as $job) {
            foreach ($job->estimators as $estimator) {
                if ($estimator->pivot->status !== 'open') {
                    continue;
                }
                $estimatorJobs[$estimator->id]['estimator'] = $estimator;
                $estimatorJobs[$estimator->id]['jobs'][]    = $job;
            }
        }

        // If all estimators have submitted, nothing to alert
        if (empty($estimatorJobs)) {
            $this->info('No overdue jobs with open estimators — no alert sent.');
            return;
        }

        // Notify Leo & Rey — only include jobs that have at least one open estimator
        $jobsWithOpenEstimators = $overdueJobs->filter(function ($job) {
            return $job->estimators->contains(fn($e) => $e->pivot->status === 'open');
        });

        foreach (self::OVERDUE_RECIPIENTS as $index => $email) {
            if ($index > 0) {
                usleep(600000);
            }
            Mail::to($email)->send(new JobOverdueMail($jobsWithOpenEstimators));
        }

        // Notify each estimator with only their own open overdue jobs
        foreach ($estimatorJobs as $entry) {
            usleep(600000);
            Mail::to($entry['estimator']->email)
                ->send(new JobOverdueMail(collect($entry['jobs'])));
        }

        $this->info("Overdue alert sent for {$jobsWithOpenEstimators->count()} job(s), " . count($estimatorJobs) . " estimator(s).");
    }
}
