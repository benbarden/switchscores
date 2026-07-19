<?php

namespace App\Domain\ReviewDraft;

use App\Models\ReviewDraft;
use App\Models\ReviewSite;

use App\Domain\GameTitleMatch\MatchRule;

use App\Domain\GameTitleHash\Repository as RepoGameTitleMatch;

class ParseTitle
{
    private $logger;

    public function __construct($logger = null)
    {
        if ($logger) $this->logger = $logger;
    }

    public function logInfo($detail)
    {
        if ($this->logger) $this->logger->info($detail);
    }

    public function logError($detail)
    {
        if ($this->logger) $this->logger->error($detail);
    }

    public function parse(ReviewDraft $reviewDraft)
    {
        $repoGameTitleHash = new RepoGameTitleMatch;

        $siteId = $reviewDraft->site_id;
        $itemUrl = $reviewDraft->item_url;
        $itemTitle = $reviewDraft->item_title;

        if (!$reviewDraft->site) {
            $this->logError('Cannot find review site! ['.$siteId.']');
            return;
        }

        if ($reviewDraft->site->review_import_method == ReviewSite::REVIEW_IMPORT_BY_SCRAPER) {
            //$logger->info('Ignoring scraper items');
            return;
        }

        $siteName = $reviewDraft->site->name;

        // Prefer the feed link the draft actually came from. A site can have several
        // feeds (e.g. Switch 1 and Switch 2 category feeds) with different match rules,
        // so only the originating feed's rule can be trusted.
        $partnerFeedLink = $reviewDraft->feedLink;

        if (!$partnerFeedLink) {

            // Older drafts (and any created before feed_link_id was recorded) fall back
            // to the site's feed, which is only safe when there is exactly one.
            $partnerFeed = $reviewDraft->site->feedLinks;
            $partnerFeedCount = count($partnerFeed);
            if ($partnerFeedCount == 0) {
                $this->logError('No feed found for partner: '.$siteName);
                return;
            } elseif ($partnerFeedCount > 1) {
                $this->logError(
                    'Draft has no feed_link_id and partner has '.$partnerFeedCount.
                    ' feeds, so the match rule is ambiguous: '.$siteName.' (draft '.$reviewDraft->id.')'
                );
                return;
            }

            $partnerFeedLink = $partnerFeed[0];
        }

        $matchRulePattern = $partnerFeedLink['title_match_rule_pattern'];
        $matchRuleIndex = $partnerFeedLink['title_match_rule_index'];

        try {

            $this->logInfo('*************************************************');
            $this->logInfo("Site: $siteName ($siteId)");
            $this->logInfo('Processing item: '.$itemTitle);

            if (!$matchRulePattern) {
                $this->logError('No match rule pattern for site: '.$siteName);
                return;
            } elseif (is_null($matchRuleIndex)) {
                $this->logError('No match rule index for site: '.$siteName);
                return;
            }

            $matchRule = new MatchRule($matchRulePattern, $matchRuleIndex);
            $titleMatches = $matchRule->generateMatch($itemTitle);

            $parsedTitle = $matchRule->getParsedTitle();

            $reviewDraft->parsed_title = $parsedTitle;
            $this->logInfo("Parsed title: $parsedTitle");

            if ($titleMatches == null) {
                $this->logError('No matches found');
                return;
            }

            $this->logInfo('Checking for matches: '.var_export($titleMatches, true));

            // Can we find a game from this title?
            $gameTitleHash = $repoGameTitleHash->byTitleGroup($titleMatches);
            if ($gameTitleHash) {
                $reviewDraft->game_id = $gameTitleHash->game_id;
                $parseStatus = ReviewDraft::PARSE_STATUS_AUTO_MATCHED;
                $this->logInfo($parseStatus);
                $reviewDraft->parse_status = $parseStatus;
            } else {
                $parseStatus = ReviewDraft::PARSE_STATUS_COULD_NOT_LOCATE;
                $this->logInfo($parseStatus);
                $reviewDraft->parse_status = $parseStatus;
            }

            $reviewDraft->save();

        } catch (\Exception $e) {
            $this->logError('Got error: '.$e->getMessage());
        }
    }
}