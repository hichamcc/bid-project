<?php

namespace App\Mail;

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobDueTodayMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Allocation $allocation,
        public User $estimator,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: Job {$this->allocation->job_number} is Due Today by 5 PM EST",
            cc: [new Address('commercial.admin@artelye.com')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-due-today',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
