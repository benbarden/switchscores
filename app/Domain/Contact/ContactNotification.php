<?php

namespace App\Domain\Contact;

use App\Enums\ContactRequestType;
use App\Models\ContactSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactNotification extends Mailable
{
    public function __construct(
        protected ContactSubmission $submission
    )
    {
    }

    private function requestTypeLabel(): string
    {
        $type = ContactRequestType::tryFrom($this->submission->request_type);

        return $type ? $type->label() : $this->submission->request_type;
    }

    private function isMemberFeedback(): bool
    {
        $type = ContactRequestType::tryFrom($this->submission->request_type);

        return $type && $type->isMemberFeedback();
    }

    public function envelope(): Envelope
    {
        // Member feedback labels already start "Member feedback: ...".
        $subject = $this->isMemberFeedback()
            ? 'Switch Scores '.lcfirst($this->requestTypeLabel())
            : 'Switch Scores contact form: '.$this->requestTypeLabel();

        return new Envelope(
            replyTo: [
                new Address($this->submission->email, $this->submission->name),
            ],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.notification',
            with: [
                'Submission' => $this->submission,
                'RequestTypeLabel' => $this->requestTypeLabel(),
                'IsMemberFeedback' => $this->isMemberFeedback(),
            ]
        );
    }
}
