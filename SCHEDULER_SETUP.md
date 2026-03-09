# Workload Report Scheduler Setup

## Overview
The system sends automated workload reports to all admin users via email.

- **Biweekly report** — sent on the 1st and 16th of each month at 8:00 AM
- **Monthly report** — sent on the last day of each month at 8:00 AM

Each report includes per-estimator breakdown of: total jobs, total days, MU jobs/days, NON MU jobs/days, submitted and open counts.

---

## 1. Environment Variables

Make sure the following are set in your `.env` file:

```env
RESEND_API_KEY=re_xxxxxxxxx
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=you@yourdomain.com
MAIL_FROM_NAME="Bid Project"
```

---

## 2. Install Resend Laravel Driver

```bash
composer require resend/resend-laravel
```

---

## 3. Add Resend to config/mail.php

In the `mailers` array, add:

```php
'resend' => [
    'transport' => 'resend',
],
```

---

## 4. Activate the Laravel Scheduler (Server Cron)

Add the following cron entry on your server:

```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/your/project` with the actual path to the project root.

---

## 5. Test Manually

You can trigger reports manually at any time using:

```bash
# Send biweekly report
php artisan report:workload biweekly

# Send monthly report
php artisan report:workload monthly
```

---

## 6. Verify Scheduler is Running

```bash
php artisan schedule:list
```

You should see:
- `report:workload biweekly` scheduled on the 1st at 08:00
- `report:workload biweekly` scheduled on the 16th at 08:00
- `report:workload monthly` scheduled on the last day of the month at 08:00

---

## Notes

- Reports are sent to **all users with the `admin` role** in the system.
- The `MAIL_FROM_ADDRESS` must be a **verified sender domain** in Resend.
- Biweekly periods: **1st–15th** and **16th–end of month**.
