<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Contact\Repository as ContactRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Enums\ContactBlockType;
use App\Enums\ContactRequestType;
use App\Enums\ContactStatus;

class ContactController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ContactRepository $repoContact
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Contact submissions';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::contactList())->bindings;

        $bindings['SubmissionList'] = $this->repoContact->getAllSubmissions();
        $bindings['StatusOptions'] = ContactStatus::options();
        $bindings['RequestTypeOptions'] = ContactRequestType::options();

        return view('staff.contact.index', $bindings);
    }

    public function show($submissionId)
    {
        $submission = $this->repoContact->findSubmission($submissionId);
        if (!$submission) abort(404);

        $pageTitle = 'Contact submission';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::contactSubpage($pageTitle))->bindings;

        $bindings['Submission'] = $submission;
        $bindings['BlockDomain'] = $this->repoContact->domainForEmail($submission->email);
        $bindings['RequestTypeOptions'] = ContactRequestType::options();

        return view('staff.contact.show', $bindings);
    }

    public function archive($submissionId)
    {
        $submission = $this->repoContact->findSubmission($submissionId);
        if (!$submission) abort(404);

        $this->repoContact->setSubmissionStatus($submission, ContactStatus::ARCHIVED->value);

        return redirect(route('staff.contact.index'));
    }

    public function blockSender($submissionId)
    {
        $submission = $this->repoContact->findSubmission($submissionId);
        if (!$submission) abort(404);

        $this->repoContact->addToBlocklist(
            $submission->email,
            ContactBlockType::EMAIL->value,
            'Blocked from submission #'.$submission->id
        );
        $this->repoContact->setSubmissionStatus($submission, ContactStatus::BLOCKED->value);

        return redirect(route('staff.contact.index'));
    }

    public function blockDomain($submissionId)
    {
        $submission = $this->repoContact->findSubmission($submissionId);
        if (!$submission) abort(404);

        $domain = $this->repoContact->domainForEmail($submission->email);
        $this->repoContact->addToBlocklist(
            $domain,
            ContactBlockType::DOMAIN->value,
            'Blocked from submission #'.$submission->id
        );
        $this->repoContact->setSubmissionStatus($submission, ContactStatus::BLOCKED->value);

        return redirect(route('staff.contact.index'));
    }
}
