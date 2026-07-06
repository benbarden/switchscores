<?php

namespace App\Domain\Contact;

use App\Enums\ContactBlockType;
use App\Enums\ContactStatus;
use App\Models\ContactBlocklist;
use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class Repository
{
    // *** SUBMISSIONS *** //

    public function createSubmission(array $data): ContactSubmission
    {
        return ContactSubmission::create($data);
    }

    public function findSubmission($id): ?ContactSubmission
    {
        return ContactSubmission::find($id);
    }

    public function getAllSubmissions(): Collection
    {
        return ContactSubmission::orderBy('id', 'desc')->get();
    }

    public function getSubmissionsByStatus(string $status): Collection
    {
        return ContactSubmission::where('status', $status)->orderBy('id', 'desc')->get();
    }

    public function countNewSubmissions(): int
    {
        return ContactSubmission::where('status', ContactStatus::NEW->value)->count();
    }

    public function setSubmissionStatus(ContactSubmission $submission, string $status): void
    {
        $submission->status = $status;
        $submission->save();
    }

    // *** BLOCKLIST *** //

    public function getAllBlocklist(): Collection
    {
        return ContactBlocklist::orderBy('value')->get();
    }

    public function findBlocklistEntry($id): ?ContactBlocklist
    {
        return ContactBlocklist::find($id);
    }

    public function addToBlocklist(string $value, string $type, ?string $note = null): ContactBlocklist
    {
        return ContactBlocklist::firstOrCreate(
            ['value' => Str::lower(trim($value)), 'type' => $type],
            ['note' => $note]
        );
    }

    public function removeFromBlocklist(ContactBlocklist $entry): void
    {
        $entry->delete();
    }

    /**
     * Returns true if the given email (or its domain) is on the blocklist.
     */
    public function isBlocked(string $email): bool
    {
        $email = Str::lower(trim($email));
        $domain = Str::afterLast($email, '@');

        return ContactBlocklist::query()
            ->where(function ($query) use ($email, $domain) {
                $query->where(function ($q) use ($email) {
                    $q->where('type', ContactBlockType::EMAIL->value)->where('value', $email);
                })->orWhere(function ($q) use ($domain) {
                    $q->where('type', ContactBlockType::DOMAIN->value)->where('value', $domain);
                });
            })
            ->exists();
    }

    /**
     * Extract the domain part of an email, for the "block whole domain" action.
     */
    public function domainForEmail(string $email): string
    {
        return Str::lower(Str::afterLast(trim($email), '@'));
    }
}
