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
        // Replies go to the site's contact mailbox. If it isn't set, Laravel's default From is used.
        $contactAddress = config('services.contact.notify_address');

        return new Envelope(
            from: $contactAddress ? new Address($contactAddress, 'Ben at Switch Scores') : null,
            replyTo: $contactAddress ? [new Address($contactAddress, 'Switch Scores')] : [],
            subject: 'Invitation to join Switch Scores',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.games-companies.invite-by-email',
            with: [
                'InviteCode' => $this->inviteCode->invite_code,
            ]
        );
    }
}