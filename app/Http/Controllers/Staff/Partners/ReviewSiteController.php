<?php

namespace App\Http\Controllers\Staff\Partners;

use App\Models\Partner;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

class ReviewSiteController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
        'website_url' => 'required',
        'link_title' => 'required|max:100',
    ];

    public function showList()
    {
        $bindings = $this->getBindingsPartnersSubpage('Review sites');

        $bindings['ReviewSitesActive'] = $this->getServicePartner()->getActiveReviewSites();
        $bindings['ReviewSitesInactive'] = $this->getServicePartner()->getInactiveReviewSites();

        return view('staff.partners.review-site.list', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsReviewSitesSubpage('Add review site');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->getServicePartner()->createReviewSite(
                Partner::STATUS_ACTIVE,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id, $ratingScale,
                $request->contact_name, $request->contact_email, $request->contact_form_link,
                $request->review_code_regions, $request->review_import_method
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['ReviewImportMethodList'] = [Partner::REVIEW_IMPORT_BY_FEED, Partner::REVIEW_IMPORT_BY_SCRAPER];

        return view('staff.partners.review-site.add', $bindings);
    }

    public function edit($siteId)
    {
        $bindings = $this->getBindingsReviewSitesSubpage('Edit review site');

        $partnerData = $this->getServicePartner()->find($siteId);
        if (!$partnerData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->getServicePartner()->editReviewSite(
                $partnerData,
                $request->status,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id, $ratingScale,
                $request->contact_name, $request->contact_email, $request->contact_form_link,
                $request->review_code_regions, $request->review_import_method
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings['ReviewSiteData'] = $partnerData;
        $bindings['SiteId'] = $siteId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['FormMode'] = 'edit';

        $bindings['StatusList'] = $statusList;

        $bindings['ReviewImportMethodList'] = [Partner::REVIEW_IMPORT_BY_FEED, Partner::REVIEW_IMPORT_BY_SCRAPER];

        return view('staff.partners.review-site.edit', $bindings);
    }
}
