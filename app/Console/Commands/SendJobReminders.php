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
            ->whereDate('due_date', $today)
            ->where('status', 'open')
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No jobs due today — no reminders sent.');
            return;
        }

        $count = 0;
        foreach ($jobs as $job) {
            foreach ($job->estimators as $estimator) {
                if ($count > 0) {
                    usleep(600000); // 600ms between sends — Resend allows max 2 req/sec
                }
                Mail::to($estimator->email)->send(new JobDueTodayMail($job, $estimator));
                $count++;
            }
        }

        $this->info("Due-today reminders sent for {$jobs->count()} job(s).");
    }

    private function sendOverdueAlerts(): void
    {
        $today = Carbon::now('America/New_York')->toDateString();

        $overdueJobs = Allocation::with('estimators')
            ->whereDate('due_date', $today)
            ->where('status', 'open')
            ->get();

        if ($overdueJobs->isEmpty()) {
            $this->info('No overdue jobs — no alert sent.');
            return;
        }

        foreach (self::OVERDUE_RECIPIENTS as $index => $email) {
            if ($index > 0) {
                usleep(600000);
            }
            Mail::to($email)->send(new JobOverdueMail($overdueJobs));
        }

        $this->info("Overdue alert sent for {$overdueJobs->count()} job(s).");
    }
}
