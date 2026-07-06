<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Mail;

use App\Domain\Contact\ContactNotification;
use App\Domain\Contact\Repository as ContactRepository;
use App\Domain\Contact\TurnstileVerifier;
use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;
use App\Enums\ContactRequestType;
use App\Enums\ContactStatus;

class ContactController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|min:2|max:100',
        'email' => 'required|email|max:150',
        'request_type' => 'required|in:correction,partnership,business,other',
        'message' => 'required|min:10|max:5000',
    ];

    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private ContactRepository $repoContact,
        private TurnstileVerifier $turnstileVerifier
    )
    {
    }

    public function show(Request $request)
    {
        $pageTitle = 'Contact';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        if ($request->isMethod('post')) {
            return $this->submit($request, $bindings);
        }

        $bindings['RequestTypeOptions'] = ContactRequestType::options();
        $bindings['TurnstileSiteKey'] = config('services.turnstile.site_key');

        return view('public.contact.index', $bindings);
    }

    private function submit(Request $request, array $bindings)
    {
        // Honeypot: real users never fill this. If populated, pretend success.
        if (!empty($request->post('website'))) {
            return redirect(route('contact.success'));
        }

        $this->validate($request, $this->validationRules);

        // Cloudflare Turnstile server-side verification.
        if (!$this->turnstileVerifier->verify($request->post('cf-turnstile-response'), $request->ip())) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['turnstile' => 'Spam check failed. Please try again.']);
        }

        $email = $request->post('email');
        $isBlocked = $this->repoContact->isBlocked($email);

        // Always store the submission - flagged and hidden if blocked, so the
        // sender gets no signal and doesn't just switch address.
        $submission = $this->repoContact->createSubmission([
            'name' => $request->post('name'),
            'email' => $email,
            'request_type' => $request->post('request_type'),
            'message' => $request->post('message'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $isBlocked ? ContactStatus::BLOCKED->value : ContactStatus::NEW->value,
        ]);

        // Only notify on genuine, non-blocked submissions.
        if (!$isBlocked) {
            Mail::to(config('services.contact.notify_address'))
                ->send(new ContactNotification($submission));
        }

        return redirect(route('contact.success'));
    }

    public function success()
    {
        $pageTitle = 'Contact - Thank you';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('public.contact.success', $bindings);
    }
}
