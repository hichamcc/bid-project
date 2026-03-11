<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Biweekly report — runs on the 1st and 16th of every month at 8am
\Illuminate\Support\Facades\Schedule::command('report:workload biweekly')
    ->monthlyOn(1, '08:00')
    ->description('Biweekly workload report (first half)');

\Illuminate\Support\Facades\Schedule::command('report:workload biweekly')
    ->monthlyOn(16, '08:00')
    ->description('Biweekly workload report (second half)');

// Monthly report — runs on the last day of every month at 8am
\Illuminate\Support\Facades\Schedule::command('report:workload monthly')
    ->lastDayOfMonth('08:00')
    ->description('Monthly workload report');

// Due-today reminder — runs daily at 9am EST, notifies estimators of jobs due today
\Illuminate\Support\Facades\Schedule::command('jobs:reminders due-today')
    ->dailyAt('09:00')
    ->timezone('America/New_York')
    ->description('Send due-today reminders to estimators');

// Overdue alert — runs daily at 5:05pm EST, alerts Leo & Rey of unsubmitted jobs past 5pm deadline
\Illuminate\Support\Facades\Schedule::command('jobs:reminders overdue')
    ->dailyAt('17:05')
    ->timezone('America/New_York')
    ->description('Send overdue job alerts to admins');
