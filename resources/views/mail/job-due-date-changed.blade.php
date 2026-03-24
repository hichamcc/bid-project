<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #d97706; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 28px 32px; font-size: 15px; color: #1e293b; line-height: 1.7; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; font-size: 15px; }
        .meta strong { color: #1e293b; }
        .highlight { display: inline-block; background: #fef3c7; color: #92400e; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
        .paragraph { margin-bottom: 18px; }
        .closing { margin-top: 24px; font-size: 15px; }
        .footer { padding: 16px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Due Date Updated</h1>
        <p>The due date for your assigned job has changed.</p>
    </div>

    <div class="body">
        <div class="meta">
            <p><strong>Hi {{ $estimator->name }},</strong></p>
            <p><strong>Job Number:</strong> {{ $allocation->job_number }}</p>
            <p><strong>Job Type:</strong> {{ $allocation->job_type === 'MU' ? 'MU' : 'NON MU' }}</p>
            <p><strong>New Due Date:</strong> <span class="highlight">{{ $allocation->assigned_date->format('M d, Y') }}</span></p>
        </div>

        <div class="paragraph">
            Please note that the due date for job <strong>{{ $allocation->job_number }}</strong> has been updated.
            Make sure to adjust your schedule accordingly.
        </div>

        <div class="paragraph">
            Once you're done with the project, go to <a href="https://bid.artelye.com/estimator/workload" style="color:#1e40af; font-weight:700;">My Workload</a> and click <strong>"Mark Submitted."</strong>
        </div>

        <div class="closing">
            Thank you for your teamwork.
        </div>
    </div>

    <div class="footer">
        This is an automated notification from the Bid Project system.
    </div>
</div>
</body>
</html>
