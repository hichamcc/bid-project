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

class JobAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Allocation $allocation,
        public User $estimator,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Job Assigned — {$this->allocation->job_number}" . ($this->allocation->project_name ? " {$this->allocation->project_name}" : ''),
            cc: [new Address('commercial.admin@artelye.com')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-assigned',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
