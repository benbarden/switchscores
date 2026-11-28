<?php

namespace App\Http\Controllers\Members;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Validation\Rule;

use App\Domain\Contact\SubmissionRecorder;
use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;
use App\Enums\ContactRequestType;

/**
 * Lets a verified member send feedback without the site showing an email address.
 * The name and email come from their account.
 */
class FeedbackController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private SubmissionRecorder $submissionRecorder
    )
    {
    }

    public function show(Request $request)
    {
        $pageTitle = 'Send feedback';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $bindings['FeedbackOptions'] = ContactRequestType::memberFeedbackOptions();
        $bindings['SelectedType'] = ContactRequestType::fromMemberFeedbackArea($request->query('from'))->value;
        $bindings['MsgSuccess'] = $request->query('msg') == 'sent';

        return view('members.feedback.index', $bindings);
    }

    public function submit(Request $request)
    {
        // Honeypot: real users never fill this. If populated, pretend success.
        if (!empty($request->post('website'))) {
            return redirect(route('members.feedback.show').'?msg=sent');
        }

        $this->validate($request, [
            'request_type' => ['required', Rule::in(array_keys(ContactRequestType::memberFeedbackOptions()))],
            'message' => 'required|min:10|max:5000',
        ]);

        $currentUser = $request->user();

        $this->submissionRecorder->record([
            'user_id' => $currentUser->id,
            'name' => mb_substr((string) $currentUser->display_name, 0, 100),
            'email' => $currentUser->email,
            'request_type' => $request->post('request_type'),
            'message' => $request->post('message'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect(route('members.feedback.show').'?msg=sent');
    }
}
