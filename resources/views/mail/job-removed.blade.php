<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #dc2626; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 28px 32px; font-size: 15px; color: #1e293b; line-height: 1.7; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; font-size: 15px; }
        .meta strong { color: #1e293b; }
        .paragraph { margin-bottom: 18px; }
        .closing { margin-top: 24px; font-size: 15px; }
        .footer { padding: 16px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Assignment Removed</h1>
        <p>You have been removed from a job assignment.</p>
    </div>

    <div class="body">
        <div class="meta">
            <p><strong>Hi {{ $estimator->name }},</strong></p>
            <p><strong>Job Number:</strong> {{ $allocation->job_number }}</p>
            @if($allocation->project_name)
            <p><strong>Project Name:</strong> {{ $allocation->project_name }}</p>
            @endif
            <p><strong>Job Type:</strong> {{ $allocation->job_type === 'MU' ? 'MU' : 'NON MU' }}</p>
        </div>

        <div class="paragraph">
            Your assignment for job <strong>{{ $allocation->job_number }}{{ $allocation->project_name ? ' — ' . $allocation->project_name : '' }}</strong> has been removed.
            You are no longer responsible for this job. If you believe this was done in error,
            please contact the estimating coordinator immediately.
        </div>

        <div class="paragraph">
            If you have already started work on this job, please ensure all your progress notes
            have been logged in the system before your access is removed.
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
