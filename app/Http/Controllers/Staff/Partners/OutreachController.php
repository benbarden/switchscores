<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

use App\Partner;
use App\PartnerOutreach;

class OutreachController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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

    public function showList(Partner $partner = null)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Partner outreach';
        $bindings['PageTitle'] = 'Partner outreach';

        if ($partner) {
            $outreachList = $this->getServicePartnerOutreach()->getByPartnerId($partner->id);
        } else {
            $outreachList = $this->getServicePartnerOutreach()->getAll();
        }

        $bindings['OutreachList'] = $outreachList;
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.partners.outreach.list', $bindings);
    }

    public function add()
    {
        $servicePartnerOutreach = $this->getServicePartnerOutreach();
        $servicePartner = $this->getServicePartner();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesAdd);

            $partnerId = $request->partner_id;

            $partnerOutreach = $servicePartnerOutreach->create(
                $partnerId, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );
            $partnerOutreach->save();

            // Update last outreach for partner
            $partner = $servicePartner->find($partnerId);
            $servicePartner->editOutreach($partner, $partnerOutreach);

            return redirect(route('staff.partners.games-company.show', ['partner' => $partner]));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Add partner outreach';
        $bindings['PageTitle'] = 'Add partner outreach';
        $bindings['FormMode'] = 'add';

        $bindings['PartnerList'] = $this->getServicePartner()->getAllGamesCompanies();
        $bindings['StatusList'] = $servicePartnerOutreach->getStatusList();
        $bindings['MethodList'] = $servicePartnerOutreach->getContactMethodList();

        $urlPartnerId = $request->partnerId;
        if ($urlPartnerId) {
            $bindings['UrlPartnerId'] = $urlPartnerId;
        }

        return view('staff.partners.outreach.add', $bindings);
    }

    public function edit(PartnerOutreach $partnerOutreach)
    {
        $servicePartnerOutreach = $this->getServicePartnerOutreach();

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRulesEdit);

            $servicePartnerOutreach->edit(
                $partnerOutreach, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );

            //return redirect(route('staff.partners.outreach.list', ['partnerId' => $request->partner_id]));
            return redirect(route('staff.partners.outreach.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Edit partner outreach';
        $bindings['PageTitle'] = 'Edit partner outreach';
        $bindings['OutreachData'] = $partnerOutreach;
        $bindings['OutreachId'] = $partnerOutreach->id;

        $bindings['StatusList'] = $servicePartnerOutreach->getStatusList();
        $bindings['MethodList'] = $servicePartnerOutreach->getContactMethodList();

        return view('staff.partners.outreach.edit', $bindings);
    }
}
