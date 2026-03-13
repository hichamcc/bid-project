<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #d97706; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.9; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .deadline-banner { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; padding: 14px 20px; margin-bottom: 24px; text-align: center; }
        .deadline-banner span { font-size: 18px; font-weight: 700; color: #92400e; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #64748b; font-weight: 600; }
        .value { color: #1e293b; font-weight: 500; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; }
        .mu { background: #ede9fe; color: #6d28d9; }
        .nonmu { background: #dbeafe; color: #1d4ed8; }
        .footer { padding: 16px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>⏰ Job Due Today</h1>
        <p>This is your daily reminder — please submit before the deadline.</p>
    </div>

    <div class="body">
        <p class="greeting">Hi <strong>{{ $estimator->name }}</strong>,</p>
        <p style="font-size:14px; color:#475569;">This is a reminder that the following job is due today and must be submitted by:</p>

        <div class="deadline-banner">
            <span>5:00 PM EST today</span>
        </div>

        <div class="detail-box">
            <div class="detail-row">
                <span class="label">Job Number : </span>
                <span class="value">{{ $allocation->job_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Job Type : </span>
                <span class="value">
                    <span class="badge {{ $allocation->job_type === 'MU' ? 'mu' : 'nonmu' }}">
                        {{ $allocation->job_type === 'NON_MU' ? 'NON MU' : 'MU' }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="label">Days Required : </span>
                <span class="value">{{ $allocation->days_required }}d</span>
            </div>
            <div class="detail-row">
                <span class="label">Due Date : </span>
                <span class="value" style="color:#b45309; font-weight:700;">{{ $allocation->assigned_date->format('M d, Y') }} — TODAY</span>
            </div>
        </div>

        <p style="font-size:13px; color:#64748b;">
            Log in to the system and mark the job as <strong>Submitted</strong> once completed.
        </p>
    </div>

    <div class="footer">
        This is an automated reminder from the Bid Project system.
    </div>
</div>
</body>
</html>
