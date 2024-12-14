<?php

namespace App\Domain\User;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RequestInviteCode extends Mailable
{
    const SUBJECT_LINE = 'Member requesting an invite code';

    public function __construct(
        protected $email,
        protected $bio
    )
    {

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('ADMIN_EMAIL'), 'Ben at Switch Scores'),
            replyTo: [
                new Address('noreply@switchscores.com', 'noreply@switchscores.com'),
            ],
            subject: self::SUBJECT_LINE,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.register.request-invite-code',
            with: [
                'UserEmail' => $this->email,
                'UserBio' => $this->bio,
                'SubjectLine' => self::SUBJECT_LINE
            ]
        );
    }
}