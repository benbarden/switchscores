<?php

namespace App\Http\Controllers\Staff\Partners;

use App\Traits\StaffView;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Partner;

use App\Traits\SwitchServices;

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
        'feed_url' => 'max:255',
        'link_title' => 'required|max:100',
    ];

    public function showList()
    {
        $bindings = $this->getBindingsPartnersSubpage('Review sites');

        $bindings['ReviewSitesActiveWithFeeds'] = $this->getServicePartner()->getActiveReviewSitesWithFeeds();
        $bindings['ReviewSitesActiveNoFeeds'] = $this->getServicePartner()->getActiveReviewSitesNoFeeds();
        $bindings['ReviewSitesInactive'] = $this->getServicePartner()->getInactiveReviewSites();

        return view('staff.partners.review-site.list', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsReviewSitesSubpage('Add review site');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $allowHistoricContent = $request->allow_historic_content == 'on' ? '1' : '0';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->getServicePartner()->createReviewSite(
                Partner::STATUS_ACTIVE,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $request->feed_url, $request->feed_url_prefix,
                $ratingScale, $allowHistoricContent,
                $request->title_match_rule_pattern,
                $request->title_match_index,
                $request->contact_name, $request->contact_email, $request->contact_form_link,
                $request->review_code_regions
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings['FormMode'] = 'add';

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

            $allowHistoricContent = $request->allow_historic_content == 'on' ? '1' : '0';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->getServicePartner()->editReviewSite(
                $partnerData,
                $request->status,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $request->feed_url, $request->feed_url_prefix,
                $ratingScale, $allowHistoricContent,
                $request->title_match_rule_pattern,
                $request->title_match_index,
                $request->contact_name, $request->contact_email, $request->contact_form_link,
                $request->review_code_regions
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings['ReviewSiteData'] = $partnerData;
        $bindings['SiteId'] = $siteId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['StatusList'] = $statusList;

        $bindings['FormMode'] = 'edit';

        return view('staff.partners.review-site.edit', $bindings);
    }
}
