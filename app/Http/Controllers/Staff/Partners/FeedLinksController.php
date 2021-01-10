<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Partner;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class FeedLinksController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'feed_status' => 'required',
        'site_id' => 'required',
        'feed_url' => 'required|max:255',
        'data_type' => 'required',
        'item_node' => 'required',
        'title_match_rule_pattern' => 'required',
        'title_match_rule_index' => 'required',
        'allow_historic_content' => 'required',
    ];

    public function showList()
    {
        $bindings = $this->getBindingsPartnersSubpage('Partner feed links');

        $bindings['FeedLinks'] = $this->getServicePartnerFeedLink()->getAll();

        return view('staff.partners.feed-links.list', $bindings);
    }

    public function buildValuesArray($request)
    {
        $allowHistoricContent = $request->allow_historic_content == 'on' ? '1' : '0';

        $values = [
            'feed_status' => $request->feed_status,
            'site_id' => $request->site_id,
            'feed_url' => $request->feed_url,
            'feed_url_prefix' => $request->feed_url_prefix,
            'data_type' => $request->data_type,
            'item_node' => $request->item_node,
            'title_match_rule_pattern' => $request->title_match_rule_pattern,
            'title_match_rule_index' => $request->title_match_rule_index,
            'allow_historic_content' => $allowHistoricContent,
        ];

        return $values;
    }

    public function add()
    {
        $bindings = $this->getBindingsFeedLinksSubpage('Add feed link');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $values = $this->buildValuesArray($request);

            $this->getServicePartnerFeedLink()->create($values);

            return redirect(route('staff.partners.feed-links.list'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['FeedStatusList'] = $this->getServicePartnerFeedLink()->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->getServicePartnerFeedLink()->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->getServicePartnerFeedLink()->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->getServicePartner()->getAllReviewSites();

        return view('staff.partners.feed-links.add', $bindings);
    }

    public function edit($linkId)
    {
        $bindings = $this->getBindingsFeedLinksSubpage('Edit feed link');

        $feedLinkData = $this->getServicePartnerFeedLink()->find($linkId);
        if (!$feedLinkData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $values = $this->buildValuesArray($request);

            $this->getServicePartnerFeedLink()->edit($feedLinkData, $values);

            return redirect(route('staff.partners.feed-links.list'));

        }

        $bindings['FeedLinkData'] = $feedLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['FeedStatusList'] = $this->getServicePartnerFeedLink()->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->getServicePartnerFeedLink()->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->getServicePartnerFeedLink()->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->getServicePartner()->getAllReviewSites();

        $bindings['FormMode'] = 'edit';

        return view('staff.partners.feed-links.edit', $bindings);
    }
}
