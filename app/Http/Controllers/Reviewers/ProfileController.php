<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;

class ProfileController extends Controller
{
    use SwitchServices;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $repoReviewSite;

    /**
     * @var array
     */
    private $validationRules = [
        'contact_name' => 'max:100',
        'review_code_regions' => 'max:100'
    ];

    public function __construct(
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoReviewSite = $repoReviewSite;
    }

    public function edit()
    {
        $currentUser = resolve('User/Repository')->currentUser();

        $reviewSite = $currentUser->partner;
        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(403);

        $siteId = $reviewSite->id;

        //$partnerData = $this->repoReviewSite->find($siteId);

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

            $reviewSite->contact_name = $contactName;
            $reviewSite->contact_email = $contactEmail;
            $reviewSite->contact_form_link = $contactFormLink;
            $reviewSite->review_code_regions = $reviewCodeRegions;
            $reviewSite->save();

            return redirect(route('reviewers.index'));
        } else {
            $bindings['FormMode'] = 'edit';
        }

        $bindings['TopTitle'] = 'Edit profile';
        $bindings['PageTitle'] = 'Edit profile';

        $bindings['PartnerData'] = $reviewSite;

        return view('reviewers.profile.edit', $bindings);
    }
}
