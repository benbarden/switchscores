<?php

namespace App\Http\Controllers\Staff\GamesCompanies;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Mail;

use App\Models\GamesCompany;
use App\Models\PartnerOutreach;

use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\InviteByEmail;
use App\Domain\InviteCode\CodeGenerator;
use App\Domain\InviteCode\Repository as InviteCodeRepository;


class InviteByEmailController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected PartnerOutreachRepository $repoPartnerOutreach,
        protected GamesCompanyRepository $repoGamesCompany,
        protected InviteCodeRepository $repoInviteCode
    )
    {
    }

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function compose(GamesCompany $gamesCompany)
    {
        $pageTitle = 'Games companies: Invite by email';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersOutreachSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        $gamesCompanyEmail = $gamesCompany['email'];
        if (!$gamesCompanyEmail) {
            return redirect(route('staff.games-companies.show', ['gamesCompany' => $gamesCompany]));
        }

        $emailSubject = 'Invitation to join Switch Scores';
        $emailBindings = [
            'InviteSubject' => $emailSubject
        ];
        $emailBodyHtml = view('emails.staff.games-companies.invite-by-email', $emailBindings);

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            // Make new invite code
            $inviteCodeGen = new CodeGenerator();
            $tempCode = $inviteCodeGen->generate();
            $repoInviteCode = new InviteCodeRepository();
            $inviteCode = $repoInviteCode->create($tempCode, 0, 1, 1, $gamesCompany->id);

            // Send the email
            $inviteByEmail = new InviteByEmail($inviteCode);
            Mail::to($gamesCompanyEmail)->send($inviteByEmail);

            // Create partner outreach
            $partnerOutreach = $this->repoPartnerOutreach->create(
                $gamesCompany->id,
                PartnerOutreach::STATUS_AWAITING_REPLY,
                PartnerOutreach::METHOD_EMAIL,
                'Automatically sent invite email via Switch Scores website',
                '',
                $inviteCode->id
            );

            // Update last outreach for partner
            $gamesCompany->last_outreach_id = $partnerOutreach->id;
            $gamesCompany->save();

            return redirect(route('staff.games-companies.show', ['gamesCompany' => $gamesCompany]));

        } else {

            $bindings['FormMode'] = 'add';

        }

        $bindings['GamesCompany'] = $gamesCompany;

        $bindings['InviteSubject'] = $emailSubject;
        $bindings['InviteBody'] = $emailBodyHtml;
        $bindings['InviteCode'] = "[[ XX WILL BE REPLACED XX ]]";

        return view('staff.games-companies.invite-by-email.compose', $bindings);
    }
}
