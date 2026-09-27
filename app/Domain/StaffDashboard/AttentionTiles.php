<?php

namespace App\Domain\StaffDashboard;

use Carbon\Carbon;

use App\Models\User;
use App\Models\UserRole;

/**
 * Builds the "Needs attention" tiles on the staff home (#163, Phase 1).
 *
 * Each tile is a plain array the Twig macro renders:
 *   key, label, count, context, state (TileState), stateLabel, url, group, suffix (optional)
 *
 * group is 'queue' (things to clear: the wide left column) or 'health' (is the site running
 * properly: the narrow right column) - Ben's layout, 2026-09-27.
 *
 * Tiles are filtered by role here rather than in Twig: a tile only appears for the roles that
 * could already see the matching row on the old staff home.
 */
class AttentionTiles
{
    public function __construct(
        private DbQueries $dbQueries,
    )
    {
    }

    /**
     * @param array $counts counts the staff home already binds:
     *   reviewDrafts, quickReviews, featuredGames, contact, signups
     */
    public function build(User $user, array $counts, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $tiles = [];

        if ($this->canSee($user, [UserRole::ROLE_REVIEWS_MANAGER])) {
            $tiles[] = $this->queueTile('review-drafts', 'Review drafts', $counts['reviewDrafts'],
                $this->dbQueries->oldestUnprocessedReviewDraft(),
                Thresholds::REVIEW_DRAFTS_AMBER_DAYS, Thresholds::REVIEW_DRAFTS_RED_DAYS,
                route('staff.reviews.review-drafts.showPending'), $now);
            $tiles[] = $this->queueTile('quick-reviews', 'Quick reviews', $counts['quickReviews'],
                $this->dbQueries->oldestPendingQuickReview(),
                Thresholds::QUICK_REVIEWS_AMBER_DAYS, Thresholds::QUICK_REVIEWS_RED_DAYS,
                route('staff.reviews.quick-reviews.list'), $now);
        }

        if ($this->canSee($user, [UserRole::ROLE_GAMES_MANAGER])) {
            $tiles[] = $this->queueTile('featured-games', 'Featured games', $counts['featuredGames'],
                $this->dbQueries->oldestPendingFeaturedGame(),
                Thresholds::FEATURED_GAMES_AMBER_DAYS, Thresholds::FEATURED_GAMES_RED_DAYS,
                route('staff.games.featured-games.list'), $now);
        }

        if ($user->isOwner()) {
            $tiles[] = $this->queueTile('contact', 'Contact submissions', $counts['contact'],
                $this->dbQueries->oldestNewContactSubmission(),
                Thresholds::CONTACT_AMBER_DAYS, Thresholds::CONTACT_RED_DAYS,
                route('staff.contact.index'), $now);
        }

        if ($this->canSee($user, [UserRole::ROLE_REVIEWS_MANAGER])) {
            $tiles[] = $this->feedsTile($now);
        }

        if ($user->isOwner()) {
            $tiles[] = $this->signupsTile($counts['signups']);
            $tiles[] = $this->jobsTile($now);
            $tiles[] = $this->integrityTile();
        }

        // Nintendo.co.uk unlinked is not a tile: it includes future-dated games, so it's never
        // meant to reach zero. It stays a row under Nintendo links on the staff home.

        return $tiles;
    }

    private function canSee(User $user, array $roles): bool
    {
        if ($user->isOwner()) {
            return true;
        }
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    private function queueTile($key, $label, $count, ?Carbon $oldest, ?int $amberDays, ?int $redDays, $url, Carbon $now): array
    {
        if ($count == 0) {
            $oldest = null;
        }
        $state = TileState::forAge($oldest, $amberDays, $redDays, $now);

        return [
            'key' => $key,
            'label' => $label,
            'count' => $count,
            'context' => $oldest ? 'Oldest waiting '.TileState::describeAge($oldest, $now) : 'Nothing waiting',
            'state' => $state,
            'stateLabel' => TileState::queueLabel($state),
            'url' => $url,
            'group' => 'queue',
        ];
    }

    /**
     * Live feeds only (#163, decision 11). A feed is flagged if its last import failed, it hasn't
     * been imported since run recording began, it has missed a nightly run, or its match rule is
     * catching fewer titles than the threshold.
     */
    public function feedsTile(Carbon $now): array
    {
        $feeds = $this->dbQueries->liveFeedLinks();
        $staleBefore = $now->copy()->subHours(Thresholds::FEED_STALE_HOURS);

        $failed = $notRun = $stale = $lowMatch = 0;
        $flagged = 0;
        foreach ($feeds as $feed) {
            $isFailed = $feed->last_run_at !== null && $feed->was_last_run_successful == 0;
            $isNotRun = $feed->last_run_at === null;
            $isStale = !$isFailed && $feed->last_run_at !== null && $feed->last_run_at->lt($staleBefore);
            $isLowMatch = $feed->title_match_rate !== null && $feed->title_match_rate < Thresholds::FEED_MATCH_RATE_AMBER;

            $failed += $isFailed ? 1 : 0;
            $notRun += $isNotRun ? 1 : 0;
            $stale += $isStale ? 1 : 0;
            $lowMatch += $isLowMatch ? 1 : 0;
            if ($isFailed || $isNotRun || $isStale || $isLowMatch) {
                $flagged++;
            }
        }

        $total = $feeds->count();
        $parts = [];
        if ($failed) $parts[] = $failed.' failed';
        if ($stale) $parts[] = $stale.' missed a run';
        if ($notRun) $parts[] = $notRun.' not run yet';
        if ($lowMatch) $parts[] = $lowMatch.' low match rate';

        if ($failed > 0) {
            $state = TileState::RED;
            $stateLabel = 'Failing';
        } elseif ($flagged > 0) {
            $state = TileState::AMBER;
            $stateLabel = 'Check';
        } else {
            $state = TileState::OK;
            $stateLabel = 'Healthy';
        }

        return [
            'key' => 'feeds',
            'label' => 'Live feeds OK',
            'count' => $total - $flagged,
            'suffix' => '/ '.$total,
            'context' => $parts ? implode(', ', $parts) : 'All Live feeds imported and matching',
            'state' => $state,
            'stateLabel' => $stateLabel,
            'url' => route('staff.reviews.feedLinks.index'),
            'group' => 'health',
        ];
    }

    /**
     * Inferred from what key nightly jobs leave behind (#163, decision 11). Exact per-job
     * recording is #164.
     */
    public function jobsTile(Carbon $now): array
    {
        $staleBefore = $now->copy()->subHours(Thresholds::JOBS_STALE_HOURS);
        $latestFeedRun = $this->dbQueries->liveFeedLinks()->max('last_run_at');

        $signals = [
            'Nintendo import' => $this->dbQueries->latestNintendoImportCompleted(),
            'Feed import' => $latestFeedRun ? Carbon::parse($latestFeedRun) : null,
            'Integrity checks' => $this->dbQueries->latestIntegrityCheckResult(),
        ];
        $missed = [];
        foreach ($signals as $name => $at) {
            if ($at === null || $at->lt($staleBefore)) {
                $missed[] = $name;
            }
        }
        // The GSC job stamps a date only, so allow for a run earlier in the day: missed if the
        // latest snapshot is from before yesterday.
        $gscDate = $this->dbQueries->latestGscSnapshotDate();
        if ($gscDate === null || $gscDate->lt($now->copy()->subDay()->startOfDay())) {
            $missed[] = 'GSC snapshots';
        }

        $total = count($signals) + 1;
        $ran = $total - count($missed);

        return [
            'key' => 'jobs',
            'label' => 'Nightly jobs ran',
            'count' => $ran,
            'suffix' => '/ '.$total,
            'context' => $missed ? 'Missed: '.implode(', ', $missed) : 'All ran in the last day',
            'state' => $missed ? TileState::RED : TileState::OK,
            'stateLabel' => $missed ? 'Missed' : 'Ran',
            'url' => route('staff.tools.index'),
            'group' => 'health',
        ];
    }

    public function integrityTile(): array
    {
        $failing = $this->dbQueries->countFailingIntegrityChecks();
        $total = $this->dbQueries->countIntegrityChecks();

        return [
            'key' => 'integrity',
            'label' => 'Integrity checks failing',
            'count' => $failing,
            'context' => 'Of '.$total.' checks',
            'state' => $failing > 0 ? TileState::AMBER : TileState::OK,
            'stateLabel' => $failing > 0 ? 'Failing' : 'Passing',
            'url' => route('staff.data-quality.dashboard'),
            'group' => 'health',
        ];
    }

    private function signupsTile($count): array
    {
        $newest = $this->dbQueries->newestGamesCompanySignup();

        return [
            'key' => 'signups',
            'label' => 'Games company signups',
            'count' => $count,
            'context' => $newest ? 'Newest '.$newest->format('j M Y') : 'None yet',
            'state' => TileState::NONE,
            'stateLabel' => '',
            'url' => route('staff.games-company-signups.index'),
            'group' => 'queue',
        ];
    }
}
