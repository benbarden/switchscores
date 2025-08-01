<?php

namespace App\Domain\ReviewDraft;

use App\Domain\Game\Repository as RepoGame;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;
use App\Domain\ReviewDraft\Repository as RepoReviewDraft;
use App\Exceptions\Review\AlreadyImported;
use App\Models\ReviewSite;
use App\Models\ReviewDraft;

class ImportScraper
{
    /**
     * @var RepoReviewDraft
     */
    private $repoReviewDraft;

    /**
     * @var ReviewDraftBuilder
     */
    private $reviewDraftBuilder;

    /**
     * @var ReviewDraftDirector
     */
    private $reviewDraftDirector;

    public function __construct(
        private RepoGame $repoGame
    )
    {
        $this->reviewDraftBuilder = new ReviewDraftBuilder();
        $this->reviewDraftDirector = new ReviewDraftDirector($this->reviewDraftBuilder);
        $this->repoReviewDraft = new RepoReviewDraft();
    }

    private function processItem($item, ReviewSite $reviewSite, $mappings, $reviewYear = null)
    {
        if (!array_key_exists('item_title', $mappings)) {
            throw new \Exception('Fatal error: Required mapping item_title not found.');
        }
        if (!array_key_exists('item_date', $mappings)) {
            throw new \Exception('Fatal error: Required mapping item_date not found.');
        }
        if (!array_key_exists('item_rating', $mappings)) {
            throw new \Exception('Fatal error: Required mapping item_rating not found.');
        }
        $idxItemTitle = $mappings['item_title'];
        $idxItemDate = $mappings['item_date'];
        $idxItemRating = $mappings['item_rating'];

        if ($item[$idxItemTitle]['text'] && $item[$idxItemTitle]['url']) {
            $itemTitle = $item[$idxItemTitle]['text'];
            $itemUrl = $item[$idxItemTitle]['url'];
        } else {
            throw new \Exception('Fatal error - Missing item title and/or URL ['.var_export($item, true).']');
        }

        if ($reviewSite->id == ReviewSite::SITE_NINTENDO_WORLD_REPORT) {
            $itemUrl = $reviewSite->website_url.substr($itemUrl, 1);
        }

        if ($reviewYear == null) {
            $itemDate = date('Y-m-d', strtotime($item[$idxItemDate]));
        } else {
            $reviewDate = $item[$idxItemDate].' '.$reviewYear;
            $itemDate = date('Y-m-d', strtotime($reviewDate));
        }

        $itemRating = $item[$idxItemRating];

        // Include non-rated reviews for Pocket Tactics as their table isn't always up to date
        if ($reviewSite->id == ReviewSite::SITE_NINTENDO_WORLD_REPORT) {
            if ($itemRating == "-") {
                throw new \Exception('No rating; skipping: '.$itemUrl);
            } elseif ($itemRating == "") {
                throw new \Exception('No rating; skipping: '.$itemUrl);
            }
        } elseif ($reviewSite->id == ReviewSite::SITE_POCKET_TACTICS) {
            $itemRating = null;
        }

        $dbExistingItem = $this->repoReviewDraft->getByItemUrl($itemUrl);
        if ($dbExistingItem) {
            throw new AlreadyImported('Already imported: '.$itemUrl);
        }

        $reviewDraft = [
            'site_id' => $reviewSite->id,
            'item_title' => $itemTitle,
            'item_url' => $itemUrl,
            'item_date' => $itemDate,
            'item_rating' => $itemRating,
        ];

        $game = $this->repoGame->getByTitle($itemTitle);
        if ($game) {
            $reviewDraft['game_id'] = $game->id;
            $reviewDraft['parse_status'] = ReviewDraft::PARSE_STATUS_AUTO_MATCHED;
        }

        $this->reviewDraftDirector->buildNewScraper($reviewDraft);
        $this->reviewDraftDirector->save();

        return $this->reviewDraftDirector->getReviewDraft();
    }

    public function processItemNWR($item, ReviewSite $reviewSite)
    {
        // Skip header row
        if ($item[0]['text'] == 'Name') {
            return false;
        }

        $mappings = [
            'item_title' => 0,
            'platform' => 1,
            'sub-platform' => 2,
            'author' => 3,
            'item_date' => 4,
            'item_rating' => 5,
        ];

        return $this->processItem($item, $reviewSite, $mappings);
    }

    public function processItemPocketTactics($item, ReviewSite $reviewSite, $reviewYear)
    {
        // Skip header row
        if ($item[0] == 'Title') {
            return false;
        }

        $mappings = [
            'item_title' => 0,
            'item_date' => 1,
            'item_rating' => 2,
        ];

        return $this->processItem($item, $reviewSite, $mappings, $reviewYear);
    }
}