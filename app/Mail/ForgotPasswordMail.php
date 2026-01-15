<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $expiration_time;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $email, $expiration_time)
    {
        $this->token = $token;
        $this->email = $email;
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
            view: 'emails.forgot_password',
            with: [
                'token' => $this->token,
                'url' => env('FRONTEND_URL', 'http://127.0.0.1:8000')
                    . '/user/reset-password?token=' . $this->token
                    . '&email=' . urlencode($this->email),
                'expiration_time' => $this->expiration_time->toDateTimeString(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
