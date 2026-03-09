<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkloadReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $period,
        public string $periodLabel,
        public \Illuminate\Support\Collection $reportData,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Workload Report — {$this->periodLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.workload-report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
