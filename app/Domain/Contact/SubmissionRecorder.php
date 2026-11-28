<?php

namespace App\Domain\Contact;

use App\Enums\ContactStatus;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Stores a message from the contact form or the member feedback form, and emails it to the
 * site's contact mailbox unless the sender is on the blocklist.
 */
class SubmissionRecorder
{
    public function __construct(
        private Repository $repoContact
    )
    {
    }

    /**
     * @param array $data name, email, request_type, message, ip, user_agent, and optionally user_id
     */
    public function record(array $data): ContactSubmission
    {
        $isBlocked = $this->repoContact->isBlocked($data['email']);

        // Always store the submission - flagged and hidden if blocked, so the
        // sender gets no signal and doesn't just switch address.
        $data['status'] = $isBlocked ? ContactStatus::BLOCKED->value : ContactStatus::NEW->value;
        $submission = $this->repoContact->createSubmission($data);

        if ($isBlocked) {
            return $submission;
        }

        $notifyAddress = config('services.contact.notify_address');
        if (empty($notifyAddress)) {
            // The message is safe in the staff inbox; don't fail the sender's request over config.
            Log::warning('Contact submission '.$submission->id.' not emailed: CONTACT_NOTIFY_ADDRESS is not set.');
            return $submission;
        }

        Mail::to($notifyAddress)->send(new ContactNotification($submission));

        return $submission;
    }
}
