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
        .body { padding: 28px 32px; font-size: 15px; color: #1e293b; line-height: 1.7; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; font-size: 15px; }
        .meta strong { color: #1e293b; }
        .paragraph { margin-bottom: 18px; }
        .screenshot { margin: 24px 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .screenshot-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; }
        .screenshot-header span { font-size: 13px; font-weight: 700; color: #475569; }
        .screenshot-body { padding: 14px 16px; background: #fff; }
        .mock-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .mock-table th { background: #f1f5f9; color: #64748b; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #e2e8f0; }
        .mock-table td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .mock-table tr:last-child td { border-bottom: none; }
        .btn { display: inline-block; background: #16a34a; color: #fff; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 4px; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 10px; font-weight: 700; }
        .mu { background: #ede9fe; color: #6d28d9; }
        .nonmu { background: #dbeafe; color: #1d4ed8; }
        .open-badge { display: inline-block; background: #fef9c3; color: #92400e; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; }
        .closing { margin-top: 24px; font-size: 15px; }
        .footer { padding: 16px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>New Job Assigned</h1>
        <p>You have been assigned a new estimating job.</p>
    </div>

    <div class="body">
        <div class="meta">
            <p><strong>Project:</strong> {{ $allocation->job_number }}</p>
            <p><strong>Due Date:</strong> {{ $allocation->due_date->format('M d, Y') }}</p>
        </div>

        <div class="paragraph">
            Review the project details in the system and proceed with the estimating process accordingly.
            If you foresee any scheduling conflicts or require clarification, notify the estimating coordinator
            immediately within 24 hours.
        </div>

        <div class="paragraph">
            Make sure to add the job to our database and log your time to the project when you work on it, as usual.
            Also make sure to complete the submit process after completing the takeoffs.
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
