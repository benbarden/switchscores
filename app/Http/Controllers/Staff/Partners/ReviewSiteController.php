<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Partner;

use App\Traits\SwitchServices;

class ReviewSiteController extends Controller
{
    use SwitchServices;

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
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Review sites';
        $bindings['PageTitle'] = 'Review sites';

        $bindings['ReviewSitesActiveWithFeeds'] = $servicePartner->getActiveReviewSitesWithFeeds();
        $bindings['ReviewSitesActiveNoFeeds'] = $servicePartner->getActiveReviewSitesNoFeeds();
        $bindings['ReviewSitesInactive'] = $servicePartner->getInactiveReviewSites();

        return view('staff.partners.review-site.list', $bindings);
    }

    public function add()
    {
        $servicePartner = $this->getServicePartner();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $allowHistoricContent = $request->allow_historic_content == 'on' ? '1' : '0';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $servicePartner->createReviewSite(
                Partner::STATUS_ACTIVE,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $request->feed_url, $request->feed_url_prefix,
                $ratingScale, $allowHistoricContent,
                $request->title_match_rule_pattern,
                $request->title_match_index
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Review sites - Add site';
        $bindings['PageTitle'] = 'Add site';
        $bindings['FormMode'] = 'add';

        return view('staff.partners.review-site.add', $bindings);
    }

    public function edit($siteId)
    {
        $servicePartner = $this->getServicePartner();

        $partnerData = $servicePartner->find($siteId);
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

            $servicePartner->editReviewSite(
                $partnerData,
                $request->status,
                $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $request->feed_url, $request->feed_url_prefix,
                $ratingScale, $allowHistoricContent,
                $request->title_match_rule_pattern,
                $request->title_match_index
            );

            return redirect(route('staff.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Review sites - Edit site';
        $bindings['PageTitle'] = 'Edit site';
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
