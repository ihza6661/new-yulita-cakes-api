<?php

namespace App\Mail;

use App\Models\SiteUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public SiteUser $user;
    public string $resetLink;

    public function __construct(SiteUser $user, string $resetLink)
    {
        $this->user = $user;
        $this->resetLink = $resetLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Notification',
            to: [$this->user->email]
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reset-markdown',
            with: [ // Data untuk view
                'user' => $this->user,
                'resetLink' => $this->resetLink,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
