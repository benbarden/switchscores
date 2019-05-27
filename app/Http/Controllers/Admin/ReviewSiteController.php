<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;
use App\Partner;

class ReviewSiteController extends Controller
{
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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Review sites';
        $bindings['PageTitle'] = 'Review sites';

        $bindings['ReviewSitesActive'] = $servicePartner->getActiveReviewSites();
        $bindings['ReviewSitesInactive'] = $servicePartner->getInactiveReviewSites();

        return view('admin.partners.review-site.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();

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

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Review sites - Add site';
        $bindings['PageTitle'] = 'Add site';
        $bindings['FormMode'] = 'add';

        return view('admin.partners.review-site.add', $bindings);
    }

    public function edit($siteId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();

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

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Review sites - Edit site';
        $bindings['PageTitle'] = 'Edit site';
        $bindings['ReviewSiteData'] = $partnerData;
        $bindings['SiteId'] = $siteId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['StatusList'] = $statusList;

        $bindings['FormMode'] = 'edit';

        return view('admin.partners.review-site.edit', $bindings);
    }
}
