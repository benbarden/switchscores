<?php

namespace App\Domain\GamesCompany;

use App\Models\InviteCode;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InviteByEmail extends Mailable
{
    public function __construct(
        protected InviteCode $inviteCode
    )
    {

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('ben@switchscores.com', 'Ben at Switch Scores'),
            replyTo: [
                new Address('ben@switchscores.com', 'ben@switchscores.com'),
            ],
            subject: 'Invitation to join Switch Scores',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.games-companies.invite-by-email',
            with: [
                'InviteCode' => $this->inviteCode->invite_code
            ]
        );
    }
}