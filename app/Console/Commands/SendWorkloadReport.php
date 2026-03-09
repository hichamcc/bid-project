<?php

namespace App\Console\Commands;

use App\Mail\WorkloadReportMail;
use App\Models\Allocation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWorkloadReport extends Command
{
    protected $signature   = 'report:workload {period : monthly or biweekly}';
    protected $description = 'Send workload report to all admins';

    public function handle(): void
    {
        $period = $this->argument('period');

        [$startDate, $endDate, $periodLabel] = match ($period) {
            'monthly'  => $this->monthlyRange(),
            'biweekly' => $this->biweeklyRange(),
            default    => throw new \InvalidArgumentException("Period must be 'monthly' or 'biweekly'"),
        };

        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])
            ->orderBy('name')
            ->get();

        $reportData = $estimators->map(function ($estimator) use ($startDate, $endDate) {
            $allocations = Allocation::whereHas('estimators', fn($q) => $q->where('users.id', $estimator->id))
                ->whereBetween('due_date', [$startDate, $endDate])
                ->get();

            $mu    = $allocations->where('job_type', 'MU');
            $nonMu = $allocations->where('job_type', 'NON_MU');

            return [
                'estimator'      => $estimator,
                'total_jobs'     => $allocations->count(),
                'total_days'     => $allocations->sum('days_required'),
                'mu_jobs'        => $mu->count(),
                'mu_days'        => $mu->sum('days_required'),
                'non_mu_jobs'    => $nonMu->count(),
                'non_mu_days'    => $nonMu->sum('days_required'),
                'submitted_jobs' => $allocations->where('status', 'submitted')->count(),
                'open_jobs'      => $allocations->where('status', 'open')->count(),
            ];
        });

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new WorkloadReportMail($period, $periodLabel, $reportData));
        }

        $this->info("Workload report ({$period}) sent to {$admins->count()} admin(s).");
    }

    private function monthlyRange(): array
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();
        $label = now()->format('F Y');

        return [$start, $end, $label];
    }

    private function biweeklyRange(): array
    {
        $day = now()->day;

        if ($day <= 15) {
            $start = now()->startOfMonth();
            $end   = now()->startOfMonth()->setDay(15)->endOfDay();
            $label = now()->format('M') . ' 1–15 ' . now()->year;
        } else {
            $start = now()->startOfMonth()->setDay(16);
            $end   = now()->endOfMonth();
            $label = now()->format('M') . ' 16–' . now()->endOfMonth()->day . ' ' . now()->year;
        }

        return [$start, $end, $label];
    }
}
