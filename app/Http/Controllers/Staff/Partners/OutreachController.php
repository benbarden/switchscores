<?php

namespace App\Http\Controllers\Staff\Partners;

use App\Models\Partner;
use App\Models\PartnerOutreach;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

class OutreachController extends Controller
{
    use SwitchServices;
    use StaffView;

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
        $bindings = $this->getBindingsPartnersSubpage('Partner outreach');

        if ($partner) {
            $outreachList = $this->getServicePartnerOutreach()->getByPartnerId($partner->id);
        } else {
            $outreachList = $this->getServicePartnerOutreach()->getAll();
        }

        $bindings['OutreachList'] = $outreachList;

        return view('staff.partners.outreach.list', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsPartnersOutreachSubpage('Add partner outreach');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesAdd);

            $partnerId = $request->partner_id;

            $partnerOutreach = $this->getServicePartnerOutreach()->create(
                $partnerId, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );
            $partnerOutreach->save();

            // Update last outreach for partner
            $partner = $this->getServicePartner()->find($partnerId);
            $this->getServicePartner()->editOutreach($partner, $partnerOutreach);

            return redirect(route('staff.partners.games-company.show', ['partner' => $partner]));

        }

        $bindings['FormMode'] = 'add';

        $bindings['PartnerList'] = $this->getServicePartner()->getAllGamesCompanies();
        $bindings['StatusList'] = $this->getServicePartnerOutreach()->getStatusList();
        $bindings['MethodList'] = $this->getServicePartnerOutreach()->getContactMethodList();

        $urlPartnerId = $request->partnerId;
        if ($urlPartnerId) {
            $bindings['UrlPartnerId'] = $urlPartnerId;
        }

        return view('staff.partners.outreach.add', $bindings);
    }

    public function edit(PartnerOutreach $partnerOutreach)
    {
        $bindings = $this->getBindingsPartnersOutreachSubpage('Edit partner outreach');

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRulesEdit);

            $this->getServicePartnerOutreach()->edit(
                $partnerOutreach, $request->new_status, $request->contact_method, $request->contact_message, $request->internal_notes
            );

            //return redirect(route('staff.partners.outreach.list', ['partnerId' => $request->partner_id]));
            return redirect(route('staff.partners.outreach.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['OutreachData'] = $partnerOutreach;
        $bindings['OutreachId'] = $partnerOutreach->id;

        $bindings['StatusList'] = $this->getServicePartnerOutreach()->getStatusList();
        $bindings['MethodList'] = $this->getServicePartnerOutreach()->getContactMethodList();

        return view('staff.partners.outreach.edit', $bindings);
    }
}
