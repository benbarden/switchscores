<?php

namespace App\Http\Controllers\Members\Reviewers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Validator;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class ProfileController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'contact_name' => 'max:100',
        'review_code_regions' => 'max:100'
    ];

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function edit()
    {
        $pageTitle = 'Edit profile';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

        $currentUser = resolve('User/Repository')->currentUser();

        $reviewSite = $currentUser->partner;
        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(403);

        $siteId = $reviewSite->id;

        //$partnerData = $this->repoReviewSite->find($siteId);

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

        $bindings['PartnerData'] = $reviewSite;

        return view('members.reviewers.profile.edit', $bindings);
    }
}
