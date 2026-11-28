<?php

namespace App\Domain\User;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyEmail extends Mailable
{
    const SUBJECT_LINE = 'Verify your Switch Scores account';

    public function __construct(
        protected string $verificationUrl
    )
    {
    }

    public function envelope(): Envelope
    {
        // If no no-reply address is set, Laravel's default From is used.
        $noreplyAddress = config('services.contact.noreply_address');

        return new Envelope(
            from: $noreplyAddress ? new Address($noreplyAddress, 'Switch Scores') : null,
            subject: self::SUBJECT_LINE,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.verify-email',
            with: [
                'VerificationUrl' => $this->verificationUrl,
                'SubjectLine' => self::SUBJECT_LINE
            ]
        );
    }
}
