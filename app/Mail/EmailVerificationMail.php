<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $expiration_time;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $expiration_time)
    {
        $this->token = $token;
        $this->expiration_time = $expiration_time;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('email_template.email_subject', ['project' => config('app.name')])
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email_verification',
            with: [
                'token' => $this->token,
                'url' => env('FRONTEND_URL', 'http://127.0.0.1:8000') . '/user/verify-email?token=' . $this->token,
                'expiration_time' => $this->expiration_time->toDateTimeString(),
            ]
        );
    }

    /**
     * Get the attachments for the message.email.verify
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
