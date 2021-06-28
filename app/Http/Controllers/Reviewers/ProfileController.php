<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ProfileController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'contact_name' => 'max:100',
        'review_code_regions' => 'max:100'
    ];

    public function edit()
    {
        $siteId = $this->getCurrentUserReviewSiteId();
        if (!$siteId) abort(403);

        $partnerData = $this->getServicePartner()->find($siteId);

        $bindings = [];
        $request = request();

        if ($request->isMethod('post')) {

            // Run initial validation rules
            $validator = Validator::make($request->all(), $this->validationRules);
            if ($validator->fails()) {
                return redirect(route('reviewers.profile.edit'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed
            $contactName = $request->contact_name;
            $contactEmail = $request->contact_email;
            $contactFormLink = $request->contact_form_link;
            $reviewCodeRegions = $request->review_code_regions;

            $partnerData->contact_name = $contactName;
            $partnerData->contact_email = $contactEmail;
            $partnerData->contact_form_link = $contactFormLink;
            $partnerData->review_code_regions = $reviewCodeRegions;
            $partnerData->save();

            return redirect(route('reviewers.index'));
        } else {
            $bindings['FormMode'] = 'edit';
        }

        $bindings['TopTitle'] = 'Edit profile';
        $bindings['PageTitle'] = 'Edit profile';

        $bindings['PartnerData'] = $partnerData;

        return view('reviewers.profile.edit', $bindings);
    }
}
