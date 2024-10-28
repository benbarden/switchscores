<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Traits\SwitchServices;

use App\Domain\InviteCode\Repository as InviteCodeRepository;
use App\Domain\InviteCode\CodeGenerator;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class InviteCodeController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'invite_code' => 'required',
    ];

    private $validationRulesGenerator = [
        'codes_count' => 'required|numeric|min:1|max:10',
    ];

    public function __construct(
        private InviteCodeRepository $repoInviteCode,
        private GamesCompanyRepository $repoGamesCompany,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Invite codes';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['InviteCodeList'] = $this->repoInviteCode->getAll();

        return view('staff.invite-code.list', $bindings);
    }

    public function generateCodes()
    {
        $pageTitle = 'Generate invite codes';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->inviteCodesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        $generator = new CodeGenerator();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesGenerator);

            $codesCount = $request->codes_count;

            $gamesCompanyId = null;
            $reviewerId = null;

            for ($i=1; $i<=$codesCount; $i++) {

                $timesUsed = 0;
                $timesLeft = 1;
                $isActive = 1;
                //$inviteCode = 'ABC'.$i;
                $inviteCode = $generator->generate();

                $validator = Validator::make($request->all(), $this->validationRulesGenerator);
                try {
                    $this->repoInviteCode->create($inviteCode, $timesUsed, $timesLeft, $isActive, $gamesCompanyId, $reviewerId);
                } catch (\Exception $e) {
                    $validator->errors()->add('codes_count', 'Failed - duplicate code!');
                    return redirect(route('staff.invite-code.generate-codes'))
                        ->withErrors($validator)
                        ->withInput();
                }

            }

            return redirect(route('staff.invite-code.list'));

        }

        return view('staff.invite-code.generate-codes', $bindings);
    }

    public function addInviteCode()
    {
        $pageTitle = 'Add invite code';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->inviteCodesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.invite-code.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingRecord = $this->repoInviteCode->getByCode($request->invite_code);

            $validator->after(function ($validator) use ($existingRecord) {
                // Check for duplicates
                if ($existingRecord != null) {
                    $validator->errors()->add('title', 'Code already exists!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.invite-code.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // All ok
            $timesUsed = $request->times_used;
            $timesLeft = $request->times_left;
            if (!$timesUsed) {
                $timesUsed = 0;
            }
            if (!$timesLeft) {
                $timesLeft = 0;
            }
            $isActive = $request->is_active == 'on' ? 1 : 0;
            $gamesCompanyId = $request->games_company_id;
            $reviewerId = $request->reviewer_id;
            $this->repoInviteCode->create($request->invite_code, $timesUsed, $timesLeft, $isActive, $gamesCompanyId, $reviewerId);

            return redirect(route('staff.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        $bindings['PartnerList'] = $this->repoReviewSite->getAll();
        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getAll();

        return view('staff.invite-code.add', $bindings);
    }

    public function editInviteCode($inviteCodeId)
    {
        $pageTitle = 'Edit invite code';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->inviteCodesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $inviteCodeData = $this->repoInviteCode->find($inviteCodeId);
        if (!$inviteCodeData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $timesUsed = $request->times_used;
            $timesLeft = $request->times_left;
            if (!$timesUsed) {
                $timesUsed = 0;
            }
            if (!$timesLeft) {
                $timesLeft = 0;
            }
            $isActive = $request->is_active == 'on' ? 1 : 0;
            $gamesCompanyId = $request->games_company_id;
            $reviewerId = $request->reviewer_id;
            $this->repoInviteCode->edit($inviteCodeData, $request->invite_code, $timesUsed, $timesLeft, $isActive, $gamesCompanyId, $reviewerId);

            return redirect(route('staff.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['InviteCodeData'] = $inviteCodeData;
        $bindings['InviteCodeId'] = $inviteCodeId;

        $bindings['PartnerList'] = $this->repoReviewSite->getAll();
        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getAll();

        return view('staff.invite-code.edit', $bindings);
    }

    public function deleteInviteCode($inviteCodeId)
    {
        $pageTitle = 'Delete invite code';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->inviteCodesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $inviteCodeData = $this->repoInviteCode->find($inviteCodeId);
        if (!$inviteCodeData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoInviteCode->delete($inviteCodeId);

            // Done

            return redirect(route('staff.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['InviteCodeData'] = $inviteCodeData;
        $bindings['InviteCodeId'] = $inviteCodeId;

        return view('staff.invite-code.delete', $bindings);
    }
}