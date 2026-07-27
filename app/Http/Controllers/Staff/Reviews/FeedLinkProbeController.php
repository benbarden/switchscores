<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\DB;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Feed\FeedProbe;
use App\Domain\Feed\FeedProbeResult;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

use App\Construction\ReviewSite\ReviewSiteBuilder;
use App\Construction\ReviewSite\ReviewSiteDirector;

use App\Models\ReviewSite;
use App\Models\PartnerFeedLink;

/**
 * Onboarding screen: paste a feed URL, see what the feed needs, create the records.
 *
 * Replaces the guesswork in the two add forms, where data_type and item_node had to be
 * chosen before anything could check them, and a wrong choice produced an import that
 * silently found nothing.
 *
 * The probe proposes; the form disposes. Every detected value lands in an editable field
 * and nothing is created until the create step is submitted.
 */
class FeedLinkProbeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const SITE_MODE_NEW = 'new';
    const SITE_MODE_EXISTING = 'existing';

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite,
        private PartnerFeedLinkRepository $repoPartnerFeedLink
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Add from feed URL';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsFeedLinksSubpage($pageTitle))->bindings;

        $bindings['FeedStatusList'] = $this->repoPartnerFeedLink->getFeedStatusDropdown();
        $bindings['DataTypeList'] = $this->repoPartnerFeedLink->getDataTypeDropdown();
        $bindings['ItemNodeList'] = $this->repoPartnerFeedLink->getItemNodeDropdown();
        $bindings['ReviewSiteList'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.feed-links.probe', $bindings);
    }

    /**
     * Runs the probe and returns what it found, for the page to render and prefill from.
     *
     * A separate request from the create step so the feed is fetched once, when asked for,
     * rather than on every edit of the form beneath it.
     */
    public function run()
    {
        $request = request();

        $feedUrl = trim($request->input('feed_url'));

        if ($feedUrl === '') {
            return response()->json(['ok' => false, 'error' => 'Enter a feed URL first.']);
        }

        $result = (new FeedProbe())->probe($feedUrl);

        if ($result->hasLoadError()) {
            return response()->json([
                'ok' => false,
                'error' => $result->getLoadError(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'feed_url' => $feedUrl,
            'item_count' => $result->getItemCount(),
            'detections' => $this->presentDetections($result),
            'values' => $this->prefillValues($result),
            'warnings' => $result->getWarnings(),
            'sample_titles' => array_slice($result->getSampleTitles(), 0, 10),
        ]);
    }

    /**
     * Creates the review site (or reuses one) and its feed link, in a transaction.
     *
     * Both records or neither: a site with no feed link looks like a manual-submission
     * partner and would sit in the review sites list looking finished, which is a worse
     * state to recover from than an outright failure.
     */
    public function create()
    {
        $request = request();

        $siteMode = $request->input('site_mode') == self::SITE_MODE_EXISTING
            ? self::SITE_MODE_EXISTING
            : self::SITE_MODE_NEW;

        $this->validate($request, $this->validationRules($siteMode));

        $feedLinkId = null;

        DB::transaction(function () use ($request, $siteMode, &$feedLinkId) {

            $siteId = $siteMode == self::SITE_MODE_EXISTING
                ? $request->input('site_id')
                : $this->createReviewSite($request)->id;

            $feedLink = $this->repoPartnerFeedLink->create([
                'feed_status' => $request->input('feed_status'),
                'site_id' => $siteId,
                'feed_url' => $request->input('feed_url'),
                'feed_url_prefix' => $request->input('feed_url_prefix'),
                'data_type' => $request->input('data_type'),
                'item_node' => $request->input('item_node'),
                'title_match_rule_pattern' => $request->input('title_match_rule_pattern'),
                'title_match_rule_index' => $request->input('title_match_rule_index'),
                'allow_historic_content' => $request->input('allow_historic_content') == 'on' ? '1' : '0',
            ]);

            $feedLinkId = $feedLink->id;
        });

        // Straight to the title rule tester rather than the index. The probe can only say
        // whether a rule matches the titles; whether those parsed titles find games is the
        // next question, and it is the one that decides if the feed is actually working.
        if ($feedLinkId) {
            return redirect(route('staff.reviews.feedLinks.testTitleRule', ['linkId' => $feedLinkId]).'?source=live');
        }

        return redirect(route('staff.reviews.feedLinks.index'));
    }

    private function validationRules($siteMode)
    {
        $rules = [
            'feed_url' => 'required|max:255',
            'feed_status' => 'required',
            'data_type' => 'required',
            'item_node' => 'required',
        ];

        if ($siteMode == self::SITE_MODE_EXISTING) {
            $rules['site_id'] = 'required';
            return $rules;
        }

        $rules['name'] = 'required|max:100';
        $rules['link_title'] = 'required|max:100';
        $rules['website_url'] = 'required';
        $rules['rating_scale'] = 'required';

        return $rules;
    }

    private function createReviewSite($request)
    {
        $reviewSiteDirector = new ReviewSiteDirector();
        $reviewSiteBuilder = new ReviewSiteBuilder();
        $reviewSiteDirector->setBuilder($reviewSiteBuilder);

        $params = $request->post();

        // Set rather than offered: this screen exists to onboard a feed, so the import
        // method is not a decision the operator needs to make here.
        $params['review_import_method'] = ReviewSite::REVIEW_IMPORT_BY_FEED;

        $reviewSiteDirector->buildNew($params);

        $reviewSite = $reviewSiteBuilder->getReviewSite();
        $reviewSite->save();

        return $reviewSite;
    }

    /**
     * Detections as rows for display: what was found, and why the probe thinks so.
     */
    private function presentDetections(FeedProbeResult $result)
    {
        $rows = [];

        $labels = [
            'item_node' => 'Item node',
            'data_type' => 'Parse mode',
            'name' => 'Site name',
            'link_title' => 'Link title',
            'website_url' => 'Site URL',
            'rating_scale' => 'Rating scale',
            'items_with_score' => 'Items with a score',
            'title_match_rule_pattern' => 'Title match rule',
        ];

        foreach ($labels as $field => $label) {

            if (!$result->hasDetection($field)) {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => $this->describeValue($field, $result->getDetection($field)),
                'evidence' => $result->getEvidence($field),
            ];
        }

        return $rows;
    }

    private function describeValue($field, $value)
    {
        if ($field == 'item_node') {
            $feedLink = new PartnerFeedLink();
            $feedLink->item_node = $value;
            return $feedLink->getItemNodeDesc();
        }

        if ($field == 'data_type') {
            $feedLink = new PartnerFeedLink();
            $feedLink->data_type = $value;
            return $feedLink->getDataTypeDesc();
        }

        return (string) $value;
    }

    /**
     * The values to drop into the form. Keyed by form field name so the page does not need
     * its own mapping.
     */
    private function prefillValues(FeedProbeResult $result)
    {
        $fields = [
            'name', 'link_title', 'website_url', 'rating_scale',
            'data_type', 'item_node', 'title_match_rule_pattern', 'title_match_rule_index',
        ];

        $values = [];

        foreach ($fields as $field) {
            $values[$field] = $result->getDetection($field);
        }

        return $values;
    }
}
