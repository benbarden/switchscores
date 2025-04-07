<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

class FeedLinksController extends Controller
{
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
    ];

    public function __construct(
        private ReviewSiteRepository $repoReviewSite,
        private PartnerFeedLinkRepository $repoPartnerFeedLink
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Feed links';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $feedLinksActive = $this->repoPartnerFeedLink->getActive();
        $feedLinksInactive = $this->repoPartnerFeedLink->getInactive();

        $bindings['FeedLinksActive'] = $feedLinksActive;
        $bindings['FeedLinksInactive'] = $feedLinksInactive;

        return view('staff.reviews.feed-links.index', $bindings);
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
        $pageTitle = 'Add feed link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsFeedLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $values = $this->buildValuesArray($request);

            $this->repoPartnerFeedLink->create($values);

            return redirect(route('staff.reviews.feedLinks.index'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['FeedStatusList'] = $this->repoPartnerFeedLink->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->repoPartnerFeedLink->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->repoPartnerFeedLink->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.feed-links.add', $bindings);
    }

    public function edit()
    {
        $request = request();

        $linkId = $request->linkId;

        $feedLink = $this->repoPartnerFeedLink->find($linkId);
        if (!$feedLink) abort(404);

        $pageTitle = 'Edit feed link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsFeedLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $linkId = $feedLink->id;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $values = $this->buildValuesArray($request);

            $this->repoPartnerFeedLink->edit($feedLink, $values);

            return redirect(route('staff.reviews.feedLinks.index'));

        }

        $bindings['FeedLinkData'] = $feedLink;
        $bindings['LinkId'] = $linkId;

        $bindings['FeedStatusList'] = $this->repoPartnerFeedLink->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->repoPartnerFeedLink->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->repoPartnerFeedLink->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->repoReviewSite->getAll();

        $bindings['FormMode'] = 'edit';

        return view('staff.reviews.feed-links.edit', $bindings);
    }
}
