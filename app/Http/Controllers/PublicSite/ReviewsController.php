<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class ReviewsController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landingByYear($year)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Review stats - '.$year;
        $bindings['PageTitle'] = 'Review stats - '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage('Review stats - '.$year);

        $bindings['ReviewYear'] = $year;

        // Review counts
        $dateList = $this->getServiceGameCalendar()->getAllowedDates(false);
        $dateListArray = [];
        $reviewTotal = 0;

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                if ($dateYear != $year) continue;

                $reviewLinkStat = $this->getServiceReviewLink()->countActiveByYearMonth($dateYear, $dateMonth);
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
        $bindings['ScoreDistributionByYear'] = $this->getServiceReviewLink()->getFullScoreDistributionByYear($year);

        // Ranked/Unranked count
        $bindings['RankedCountByYear'] = $this->getServiceTopRated()->getRankedCountByYear($year);

        // Review count stats
        $bindings['ReviewCountStatsByYear'] = $this->getServiceReviewLink()->getReviewCountStatsByYear($year);

        $bindings['DateList'] = $dateListArray;
        $bindings['ReviewTotal'.$year] = $reviewTotal;

        return view('public.reviews.landingByYear', $bindings);
    }

}
