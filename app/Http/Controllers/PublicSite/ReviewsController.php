<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Domain\TopRated\DbQueries as DbTopRated;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;
use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;

use Illuminate\Routing\Controller as Controller;

class ReviewsController extends Controller
{
    public function __construct(
        private DbTopRated $dbTopRated,
        private Breadcrumbs $viewBreadcrumbs,
        private GameCalendarAllowedDates $allowedDates,
        private ReviewLinkStatsRepository $repoReviewLinkStats,
    )
    {
    }

    public function landingByYear($year)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Review stats - '.$year;
        $bindings['PageTitle'] = 'Review stats - '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage('Review stats - '.$year);

        $bindings['ReviewYear'] = $year;

        // Review counts
        $dateList = $this->allowedDates->allowedDates(false);
        $dateListArray = [];
        $reviewTotal = 0;

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                if ($dateYear != $year) continue;

                $reviewLinkStat = $this->repoReviewLinkStats->totalActiveByYearMonth($dateYear, $dateMonth);
                if ($reviewLinkStat) {
                    $dateCount = $reviewLinkStat;
                } else {
                    $dateCount = 0;
                }

                if ($dateCount == 0) continue;

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'ReviewCount' => $dateCount,
                ];
                $reviewTotal += $dateCount;

            }

        }

        // Score distribution
        $bindings['ScoreDistributionByYear'] = $this->repoReviewLinkStats->scoreDistributionByYear($year);

        // Ranked/Unranked count
        $bindings['RankedCountByYear'] = $this->dbTopRated->rankedCountByYear($year);

        // Review count stats
        $bindings['ReviewCountStatsByYear'] = $this->repoReviewLinkStats->reviewCountStats($year);

        $bindings['DateList'] = $dateListArray;
        $bindings['ReviewTotal'.$year] = $reviewTotal;

        return view('public.reviews.landingByYear', $bindings);
    }

}
