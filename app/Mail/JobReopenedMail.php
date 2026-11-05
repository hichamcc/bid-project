<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobReopenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $estimator,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Job Reopened — {$this->project->name}",
            cc: [new Address('commercial.admin@artelye.com')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-reopened',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
