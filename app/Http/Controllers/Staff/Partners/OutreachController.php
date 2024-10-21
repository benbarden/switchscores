<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\GamesCompany;
use App\Models\PartnerOutreach;

use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\SwitchServices;

class OutreachController extends Controller
{
    use SwitchServices;

    private $repoPartnerOutreach;
    private $repoGamesCompany;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        PartnerOutreachRepository $repoPartnerOutreach,
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->repoPartnerOutreach = $repoPartnerOutreach;
        $this->repoGamesCompany = $repoGamesCompany;
    }

    /**
     * @var array
     */
    private $validationRulesAdd = [
        'partner_id' => 'required',
        'new_status' => 'required',
    ];

    /**
     * @var array
     */
    private $validationRulesEdit = [
        'new_status' => 'required',
    ];

    public function showList(GamesCompany $gamesCompany = null)
    {
        $pageTitle = 'Partner outreach';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if ($gamesCompany) {
            $outreachList = $this->repoPartnerOutreach->byPartnerId($gamesCompany->id);
        } else {
            $outreachList = $this->repoPartnerOutreach->getAll();
        }

        $bindings['OutreachList'] = $outreachList;

        return view('staff.partners.outreach.list', $bindings);
    }

    public function add(GamesCompany $gamesCompany = null)
    {
        $pageTitle = 'Add partner outreach';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersOutreachSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesAdd);

            $partnerId = $request->partner_id;

            $partnerOutreach = $this->repoPartnerOutreach->create(
                $partnerId, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );
            $partnerOutreach->save();

            // Update last outreach for partner
            $gamesCompany = $this->repoGamesCompany->find($partnerId);
            $gamesCompany->last_outreach_id = $partnerOutreach->id;
            $gamesCompany->save();

            return redirect(route('staff.games-companies.show', ['gamesCompany' => $gamesCompany]));

        }

        $bindings['FormMode'] = 'add';

        $bindings['PartnerList'] = $this->repoGamesCompany->getAll();
        $bindings['StatusList'] = $this->repoPartnerOutreach->statusList();
        $bindings['MethodList'] = $this->repoPartnerOutreach->contactMethodList();

        if ($gamesCompany) {
            $urlPartnerId = $gamesCompany->id;
            if ($urlPartnerId) {
                $bindings['UrlPartnerId'] = $urlPartnerId;
            }
        }

        return view('staff.partners.outreach.add', $bindings);
    }

    public function edit(PartnerOutreach $partnerOutreach)
    {
        $pageTitle = 'Edit partner outreach';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersOutreachSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRulesEdit);

            $this->repoPartnerOutreach->edit(
                $partnerOutreach, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );

            //return redirect(route('staff.partners.outreach.list', ['partnerId' => $request->partner_id]));
            return redirect(route('staff.partners.outreach.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['OutreachData'] = $partnerOutreach;
        $bindings['OutreachId'] = $partnerOutreach->id;

        $bindings['StatusList'] = $this->repoPartnerOutreach->statusList();
        $bindings['MethodList'] = $this->repoPartnerOutreach->contactMethodList();

        return view('staff.partners.outreach.edit', $bindings);
    }
}
