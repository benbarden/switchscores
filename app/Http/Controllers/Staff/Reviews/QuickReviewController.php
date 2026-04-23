<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;
use App\Models\QuickReview;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;
use App\Domain\User\Repository as UserRepository;

class QuickReviewController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private QuickReviewRepository $repoQuickReview,
        private ReviewLinkStats $reviewLinkStats,
        private UserRepository $repoUser,
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Quick reviews';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        $request = request();
        $filterStatus = $request->filterStatus;

        if (!isset($filterStatus)) {
            $bindings['FilterStatus'] = '';
            $reviewList = $this->repoQuickReview->getAll();
        } else {
            $bindings['FilterStatus'] = $filterStatus;
            $reviewList = $this->repoQuickReview->byStatus($filterStatus);
        }

        $bindings['QuickReviewList'] = $reviewList;
        $bindings['QuickReviewStatusList'] = $this->repoQuickReview->getStatusList();

        return view('staff.reviews.quick-reviews.list', $bindings);
    }

    public function approve($reviewId)
    {
        $reviewData = $this->repoQuickReview->find($reviewId);
        if (!$reviewData) abort(404);

        $this->repoQuickReview->editStatus($reviewData, QuickReview::STATUS_ACTIVE);

        $userId = $reviewData->user_id;
        $gameId = $reviewData->game_id;
        $game = $this->repoGame->find($gameId);

        $this->reviewLinkStats->updateStats($game);

        $user = $this->repoUser->find($userId);
        UserFactory::addPointsForQuickReview($user);
        UserPointTransactionDirectorFactory::addForQuickReview($userId, $gameId);

        return redirect(route('staff.reviews.quick-reviews.list'));
    }

    public function reject($reviewId)
    {
        $reviewData = $this->repoQuickReview->find($reviewId);
        if (!$reviewData) abort(404);

        $this->repoQuickReview->editStatus($reviewData, QuickReview::STATUS_REJECTED);

        $gameId = $reviewData->game_id;
        $game = $this->repoGame->find($gameId);

        $this->reviewLinkStats->updateStats($game);

        return redirect(route('staff.reviews.quick-reviews.list'));
    }

    public function delete($reviewId)
    {
        $pageTitle = 'Delete quick review';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsQuickReviewsSubpage($pageTitle))->bindings;

        $reviewData = $this->repoQuickReview->find($reviewId);
        if (!$reviewData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $gameId = $reviewData->game_id;

            $this->repoQuickReview->delete($reviewId);

            $game = $this->repoGame->find($gameId);
            if ($game) {
                // Update game review stats
                $this->reviewLinkStats->updateStats($game);
            }

            // Done

            return redirect(route('staff.reviews.quick-reviews.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['QuickReview'] = $reviewData;
        $bindings['ReviewId'] = $reviewId;

        return view('staff.reviews.quick-reviews.delete', $bindings);
    }
}
