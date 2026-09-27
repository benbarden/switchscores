<?php

namespace App\Domain\StaffDashboard;

use Carbon\Carbon;

use App\Enums\ContactStatus;
use App\Models\ContactSubmission;
use App\Models\DataSourceImportRun;
use App\Models\FeaturedGame;
use App\Models\GamesCompanySignup;
use App\Models\GscPageSnapshot;
use App\Models\IntegrityCheck;
use App\Models\IntegrityCheckResult;
use App\Models\PartnerFeedLink;
use App\Models\QuickReview;
use App\Models\ReviewDraft;

/**
 * The reads behind the staff dashboard's needs-attention tiles. Counts are already bound on the
 * staff home by the existing repositories; these add the "how long has it waited" half.
 */
class DbQueries
{
    public function oldestUnprocessedReviewDraft(): ?Carbon
    {
        return $this->asCarbon(ReviewDraft::whereNull('process_status')->min('created_at'));
    }

    public function oldestPendingQuickReview(): ?Carbon
    {
        return $this->asCarbon(QuickReview::where('item_status', QuickReview::STATUS_PENDING)->min('created_at'));
    }

    public function oldestPendingFeaturedGame(): ?Carbon
    {
        return $this->asCarbon(FeaturedGame::where('status', FeaturedGame::STATUS_PENDING)->min('created_at'));
    }

    public function oldestNewContactSubmission(): ?Carbon
    {
        return $this->asCarbon(ContactSubmission::where('status', ContactStatus::NEW->value)->min('created_at'));
    }

    public function newestGamesCompanySignup(): ?Carbon
    {
        return $this->asCarbon(GamesCompanySignup::max('created_at'));
    }

    /**
     * Live feed links only. Non-Live feeds are never imported, so their run status is whatever
     * it last was and says nothing about now (#163, decision 11).
     */
    public function liveFeedLinks()
    {
        return PartnerFeedLink::where('feed_status', PartnerFeedLink::FEED_STATUS_LIVE)->get();
    }

    public function latestNintendoImportCompleted(): ?Carbon
    {
        return $this->asCarbon(DataSourceImportRun::max('completed_at'));
    }

    public function latestIntegrityCheckResult(): ?Carbon
    {
        return $this->asCarbon(IntegrityCheckResult::max('created_at'));
    }

    /**
     * The GSC snapshot job stamps the date it ran (date only), not the date of the data.
     */
    public function latestGscSnapshotDate(): ?Carbon
    {
        return $this->asCarbon(GscPageSnapshot::max('snapshot_date'));
    }

    public function countIntegrityChecks(): int
    {
        return IntegrityCheck::count();
    }

    public function countFailingIntegrityChecks(): int
    {
        return IntegrityCheck::where('is_passing', 0)->count();
    }

    private function asCarbon($value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }
}
