<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class JobOverdueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $overdueJobs,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->overdueJobs->count();
        return new Envelope(
            subject: "🔴 URGENT: {$count} Job(s) Not Submitted by Due Date",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-overdue',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
