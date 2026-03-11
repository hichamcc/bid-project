<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1e40af; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
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
        <h1>New Job Assigned</h1>
        <p>You have been assigned a new job.</p>
    </div>

    <div class="body">
        <p class="greeting">Hi <strong>{{ $estimator->name }}</strong>,</p>
        <p style="font-size:14px; color:#475569;">A new job has been assigned to you. Here are the details:</p>

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
                <span class="label">Assigned Date : </span>
                <span class="value">{{ $allocation->assigned_date->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Due Date : </span>
                <span class="value">{{ $allocation->due_date->format('M d, Y') }}</span>
            </div>
        </div>

        <p style="font-size:13px; color:#64748b;">
            Log in to the system to view your updated workload.
        </p>
    </div>

    <div class="footer">
        This is an automated notification from the Bid Project system.
    </div>
</div>
</body>
</html>
