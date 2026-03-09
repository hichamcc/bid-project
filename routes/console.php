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
