<?php

namespace App\Http\Controllers\Members\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;
use App\Domain\Unranked\Repository as UnrankedRepository;

class UnrankedGamesController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private ReviewLinkRepository $repoReviewLink,
        private ReviewLinkStats $statsReviewLink,
        private UnrankedRepository $repoUnranked
    )
    {
    }

    public function showList($mode, $filter)
    {
        $pageTitle = 'Unranked games';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;
        if ($partnerId == 0) {
            abort(403);
        }

        $allowedYears = resolve('Domain\GameCalendar\AllowedDates')->releaseYears();

        $gameIdsReviewedBySite = $this->repoReviewLink->bySiteGameIdList($partnerId);
        $totalReviewLinksBySite = $this->statsReviewLink->totalBySite($partnerId);

        switch ($mode) {

            case 'by-count':
                if (!in_array($filter, ['0', '1', '2'])) abort(404);
                $gamesList = $this->repoUnranked->getByReviewCount($filter, $gameIdsReviewedBySite);
                if ($filter == 0) {
                    $tableSort = "[3, 'asc']";
                } else {
                    $tableSort = "[5, 'desc']";
                }
                $bindings['FilterOnLoad'] = 'by-count-'.$filter;
                break;

            case 'by-year':
                if (!in_array($filter, $allowedYears)) abort(404);
                $gamesList = $this->repoUnranked->getByYear($filter, $gameIdsReviewedBySite);
                $tableSort = "[3, 'asc']";
                $bindings['FilterOnLoad'] = 'by-year-'.$filter;
                break;

            default:
                abort(404);

        }

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = $tableSort;

        $bindings['GamesReviewedCount'] = $totalReviewLinksBySite;
        $bindings['AllowedYears'] = $allowedYears;

        $bindings['PageMode'] = $mode;
        $bindings['PageFilter'] = $filter;

        return view('members.reviewers.unranked-games.list', $bindings);
    }
}
