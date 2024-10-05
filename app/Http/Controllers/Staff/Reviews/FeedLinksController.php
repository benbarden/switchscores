<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\PartnerFeedLink;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

use App\Traits\SwitchServices;

class FeedLinksController extends Controller
{
    use SwitchServices;

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

    protected $viewBreadcrumbs;
    protected $viewBindings;
    protected $repoReviewSite;
    private $repoPartnerFeedLink;

    public function __construct(
        ReviewSiteRepository $repoReviewSite,
        PartnerFeedLinkRepository $repoPartnerFeedLink
    )
    {
        $this->repoReviewSite = $repoReviewSite;
        $this->repoPartnerFeedLink = $repoPartnerFeedLink;
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

            $this->getServicePartnerFeedLink()->create($values);

            return redirect(route('staff.reviews.feedLinks.index'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['FeedStatusList'] = $this->getServicePartnerFeedLink()->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->getServicePartnerFeedLink()->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->getServicePartnerFeedLink()->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.feed-links.add', $bindings);
    }

    public function edit(PartnerFeedLink $feedLink)
    {
        $pageTitle = 'Edit feed link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsFeedLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $linkId = $feedLink->id;

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $values = $this->buildValuesArray($request);

            $this->getServicePartnerFeedLink()->edit($feedLink, $values);

            return redirect(route('staff.reviews.feedLinks.index'));

        }

        $bindings['FeedLinkData'] = $feedLink;
        $bindings['LinkId'] = $linkId;

        $bindings['FeedStatusList'] = $this->getServicePartnerFeedLink()->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->getServicePartnerFeedLink()->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->getServicePartnerFeedLink()->getItemNodeDropdown();

        $bindings['ReviewSiteList'] = $this->repoReviewSite->getAll();

        $bindings['FormMode'] = 'edit';

        return view('staff.reviews.feed-links.edit', $bindings);
    }
}
