<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\PartnerFeedLink\TestTitleRule;
use App\Domain\PartnerFeedLink\TitleMatchRate;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\ImportByFeed;

use App\Domain\Feed\Loader;

use App\Domain\Game\Repository as RepoGame;

/**
 * Read-only tester for a feed link's title match rule. Writes nothing: it exists so that
 * match/fail is visible on screen instead of only in the cron log.
 */
class FeedLinkTitleRuleController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const SOURCE_DRAFTS = 'drafts';
    const SOURCE_LIVE = 'live';

    // Shared with the stored rate, so the number on the feed links table always means the
    // same thing as the number on this page.
    const DRAFT_LIMIT = TitleMatchRate::SAMPLE_SIZE;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private PartnerFeedLinkRepository $repoPartnerFeedLink,
        private ReviewDraftRepository $repoReviewDraft,
        private RepoGame $repoGame
    )
    {
    }

    public function show($linkId)
    {
        $feedLink = $this->repoPartnerFeedLink->find($linkId);
        if (!$feedLink) abort(404);

        $pageTitle = 'Test title rule';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsFeedLinksSubpage($pageTitle))->bindings;

        $request = request();

        $source = $request->source == self::SOURCE_LIVE ? self::SOURCE_LIVE : self::SOURCE_DRAFTS;

        $titles = [];
        $loadError = null;

        try {
            $titles = $source == self::SOURCE_LIVE
                ? $this->getTitlesFromFeed($feedLink)
                : $this->getTitlesFromDrafts($feedLink);
        } catch (\Exception $e) {
            $loadError = $e->getMessage();
        }

        $bindings['FeedLinkData'] = $feedLink;
        $bindings['LinkId'] = $feedLink->id;
        $bindings['Source'] = $source;
        $bindings['LoadError'] = $loadError;
        $bindings['TitleCount'] = count($titles);
        $bindings['DraftLimit'] = self::DRAFT_LIMIT;

        return view('staff.reviews.feed-links.test-title-rule', $bindings);
    }

    /**
     * Runs a candidate pattern and returns the outcome as JSON, so the page can preview
     * edits without a reload. The matching itself stays in PHP: JavaScript regex is not
     * PCRE, and an in-browser preview would quietly disagree with what the cron does.
     */
    public function preview($linkId)
    {
        $feedLink = $this->repoPartnerFeedLink->find($linkId);
        if (!$feedLink) abort(404);

        $request = request();

        $pattern = $request->input('title_match_rule_pattern');
        $index = $request->input('title_match_rule_index');
        $source = $request->input('source') == self::SOURCE_LIVE ? self::SOURCE_LIVE : self::SOURCE_DRAFTS;

        try {
            $titles = $source == self::SOURCE_LIVE
                ? $this->getTitlesFromFeed($feedLink)
                : $this->getTitlesFromDrafts($feedLink);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Could not load titles: '.$e->getMessage(),
            ]);
        }

        $testTitleRule = new TestTitleRule();
        $testTitleRule->setRule($pattern, $index === null || $index === '' ? null : (int) $index);

        $validation = $testTitleRule->validatePattern();

        if (!$validation['valid']) {
            return response()->json([
                'ok' => false,
                'error' => $validation['error'],
                'suggestion' => $testTitleRule->suggestRule($titles),
                'title_count' => count($titles),
            ]);
        }

        $outcome = $testTitleRule->test($titles);

        return response()->json([
            'ok' => true,
            'prepared' => $validation['prepared'],
            'warnings' => $validation['warnings'],
            'results' => $outcome['results'],
            'summary' => $outcome['summary'],
            'total' => $outcome['total'],
            'match_rate' => $outcome['match_rate'],
            'suggestion' => $testTitleRule->suggestRule($titles),
        ]);
    }

    /**
     * Saves the tested rule straight onto the feed link, so a working rule doesn't have to be
     * copied through the edit screen and back. Writes only the two title rule fields, and
     * refuses anything that fails validation - a rule that cannot compile would silently
     * break parsing for the whole feed.
     */
    public function save($linkId)
    {
        $feedLink = $this->repoPartnerFeedLink->find($linkId);
        if (!$feedLink) abort(404);

        $request = request();

        $pattern = $request->input('title_match_rule_pattern');
        $index = $request->input('title_match_rule_index');

        $testTitleRule = new TestTitleRule();
        $testTitleRule->setRule($pattern, $index === null || $index === '' ? null : (int) $index);

        $validation = $testTitleRule->validatePattern();

        if (!$validation['valid']) {
            return response()->json([
                'ok' => false,
                'error' => 'Not saved - '.$validation['error'],
            ]);
        }

        $previousPattern = $feedLink->title_match_rule_pattern;
        $previousIndex = $feedLink->title_match_rule_index;

        $this->repoPartnerFeedLink->updateTitleRule($feedLink, $pattern, (int) $index);

        // Keep the rate shown on the feed links table in step with the rule just saved,
        // rather than waiting for the next scheduled run.
        $rate = (new TitleMatchRate())->update($feedLink);

        return response()->json([
            'ok' => true,
            'saved_pattern' => $pattern,
            'saved_index' => (int) $index,
            'previous_pattern' => $previousPattern,
            'previous_index' => $previousIndex,
            'match_rate' => $rate,
        ]);
    }

    private function getTitlesFromDrafts($feedLink)
    {
        $drafts = $this->repoReviewDraft->getRecentByFeedLink($feedLink->id, self::DRAFT_LIMIT);

        $titles = [];
        foreach ($drafts as $draft) {
            $titles[] = $draft->item_title;
        }

        return $titles;
    }

    /**
     * Pulls the feed live and extracts titles using the same code the importer uses, so a
     * title previewed here is the title the importer would have stored.
     */
    private function getTitlesFromFeed($feedLink)
    {
        $feedLoader = new Loader($feedLink);
        $feedLoader->loadByUrl($feedLink->feed_url);
        $itemArray = $feedLoader->buildItemArray();

        $importByFeed = new ImportByFeed($this->repoGame);
        $importByFeed->setPartnerDetails($feedLink);

        $titles = [];
        foreach ($itemArray as $item) {
            $itemData = $feedLink->isAtom()
                ? $importByFeed->buildFromAtom($item)
                : $importByFeed->buildFromRss($item);
            if (array_key_exists('item_title', $itemData)) {
                $titles[] = $itemData['item_title'];
            }
        }

        return $titles;
    }
}
