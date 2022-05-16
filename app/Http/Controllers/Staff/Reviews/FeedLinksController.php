<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\PartnerFeedLink;
use App\Domain\ViewBindings\Staff as Bindings;
use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\StaffView;
use App\Traits\SwitchServices;

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
    ];

    protected $viewBreadcrumbs;
    protected $viewBindings;
    protected $repoReviewSite;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        Bindings $viewBindings,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->viewBindings = $viewBindings;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function index()
    {
        $breadcrumbs = $this->viewBreadcrumbs->reviewsSubpage('Feed links');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Feed links');

        $feedLinks = $this->getServicePartnerFeedLink()->getAll();

        foreach ($feedLinks as &$feedLink) {

            $feedId = $feedLink->id;
            $feedImport = $this->getServiceReviewFeedImport()->getLatestByFeedId($feedId);
            if ($feedImport) {
                $feedLink->lastFeedImport = $feedImport;
            }

        }

        $bindings['FeedLinks'] = $feedLinks;

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
        $breadcrumbs = $this->viewBreadcrumbs->reviewsSubpage('Add feed link');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Add feed link');

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
        $breadcrumbs = $this->viewBreadcrumbs->reviewsSubpage('Edit feed link');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Edit feed link');

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
