<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.10); }
        .header { background: #dc2626; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: -0.3px; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.9; }
        .alert-banner { background: #fef2f2; border-left: 5px solid #dc2626; padding: 14px 20px; margin: 24px 32px 0; border-radius: 4px; }
        .alert-banner p { margin: 0; font-size: 14px; color: #991b1b; font-weight: 600; }
        .body { padding: 20px 32px 28px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        th { background: #1e293b; color: #fff; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .mu { background: #ede9fe; color: #6d28d9; }
        .nonmu { background: #dbeafe; color: #1d4ed8; }
        .overdue { color: #dc2626; font-weight: 700; }
        .footer { padding: 16px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🔴 URGENT — Jobs Not Submitted by Due Date</h1>
        <p>HIGH PRIORITY — Immediate attention required.</p>
    </div>

    <div class="alert-banner">
        <p>The following {{ $overdueJobs->count() }} job(s) have passed their due date and have NOT been submitted.</p>
    </div>

    <div class="body">
        <table>
            <thead>
                <tr>
                    <th>Job Number</th>
                    <th>Type</th>
                    <th>Days</th>
                    <th>Due Date</th>
                    <th>Estimator(s)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueJobs as $job)
                    @php
                        $estimatorNames = $job->estimators->pluck('name')->join(', ');
                    @endphp
                    <tr>
                        <td><strong>{{ $job->job_number }}{{ $job->project_name ? ' ' . $job->project_name : '' }}</strong></td>
                        <td>
                            <span class="badge {{ $job->job_type === 'MU' ? 'mu' : 'nonmu' }}">
                                {{ $job->job_type === 'NON_MU' ? 'NON MU' : 'MU' }}
                            </span>
                        </td>
                        <td>{{ $job->days_required }}d</td>
                        <td class="overdue">{{ $job->assigned_date->format('M d, Y') }}</td>
                        <td>{{ $estimatorNames ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top:24px; font-size:13px; color:#475569;">
            Please follow up with the assigned estimators immediately.
        </p>
    </div>

    <div class="footer">
        This is an automated HIGH PRIORITY alert from the Bid Project system.
    </div>
</div>
</body>
</html>
