<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

use App\Traits\SwitchServices;

class GamesBrowseController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $repoTag;
    protected $repoTagCategory;

    public function __construct(
        GameListsRepository $repoGameLists,
        TagRepository $repoTag,
        TagCategoryRepository $repoTagCategory
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->repoTag = $repoTag;
        $this->repoTagCategory = $repoTagCategory;
    }

    public function byTitleLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Browse Nintendo Switch games by title';
        $bindings['PageTitle'] = 'Browse Nintendo Switch games by title';

        $bindings['LetterList'] = range('A', 'Z');

        return view('games.browse.byTitleLanding', $bindings);
    }

    public function byTitlePage($letter)
    {
        $bindings = [];

        $gamesList = $this->getServiceGameReleaseDate()->getReleasedByLetter($letter);

        $bindings['GameList'] = $gamesList;
        $bindings['GameLetter'] = $letter;

        $bindings['TopTitle'] = 'Browse Nintendo Switch games by title: '.$letter;
        $bindings['PageTitle'] = 'Browse Nintendo Switch games by title: '.$letter;

        return view('games.browse.byTitlePage', $bindings);
    }

    public function byCategoryLanding()
    {
        $bindings = [];

        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by category';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by category';

        return view('games.browse.byCategoryLanding', $bindings);
    }

    public function byCategoryPageOld($category)
    {
        $bindings = [];

        $category = $this->getServiceCategory()->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $bindings['Category'] = $category;

        $bindings['CategoryGameCount'] = $this->getServiceCategory()->countReleasedByCategory($categoryId);
        $bindings['RankedGameList'] = $this->getServiceCategory()->getRankedByCategory($categoryId);
        $bindings['UnrankedGameList'] = $this->getServiceCategory()->getUnrankedByCategory($categoryId);

        $bindings['PageTitle'] = 'Best '.$categoryName.' Nintendo Switch games';
        $bindings['TopTitle'] = 'Best '.$categoryName.' Nintendo Switch games';

        return view('games.browse.byCategoryPage', $bindings);
    }

    public function byCategoryPage($category)
    {
        $bindings = [];

        $category = $this->getServiceCategory()->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $bindings['Category'] = $category;

        // Snapshot
        $bindings['SnapshotTopRated'] = $this->repoGameLists->rankedByCategory($categoryId, 10);
        $bindings['SnapshotNewReleases'] = $this->repoGameLists->recentlyReleasedByCategory($categoryId, 10);
        $bindings['SnapshotUnranked'] = $this->repoGameLists->unrankedByCategory($categoryId, 10);

        // Tables
        $bindings['RankedGameList'] = $this->repoGameLists->rankedByCategory($categoryId);
        $bindings['UnrankedGameList'] = $this->repoGameLists->unrankedByCategory($categoryId);
        $bindings['RankedListSort'] = "[4, 'desc']";
        $bindings['UnrankedListSort'] = "[3, 'desc'], [1, 'asc']";

        $bindings['PageTitle'] = 'Nintendo Switch '.$categoryName.' games';
        $bindings['TopTitle'] = 'Nintendo Switch '.$categoryName.' games';

        return view('games.browse.byCategoryPage', $bindings);
    }

    public function byPrimaryTypeLanding()
    {
        return redirect(route('games.browse.byCategory.landing'));
    }

    public function byPrimaryTypePage($linkTitle)
    {
        $category = $this->getServiceCategory()->getByLinkTitle($linkTitle);
        if (!$category) abort(404);

        return redirect(route('games.browse.byCategory.page', ['category' => $category->link_title]));
    }

    public function bySeriesLanding()
    {
        $bindings = [];

        $bindings['SeriesList'] = $this->getServiceGameSeries()->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by series';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by series';

        return view('games.browse.bySeriesLanding', $bindings);
    }

    public function bySeriesPage($series)
    {
        $bindings = [];

        $gameSeries = $this->getServiceGameSeries()->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $gameList = $this->getServiceGameReleaseDate()->getBySeries($seriesId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by series: '.$seriesName;
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by series: '.$seriesName;

        return view('games.browse.bySeriesPage', $bindings);
    }

    public function byTagLanding()
    {
        $bindings = [];

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by tag';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by tag';

        return view('games.browse.byTagLanding', $bindings);
    }

    public function byTagPage($tag)
    {
        $bindings = [];

        $tag = $this->getServiceTag()->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $gameList = $this->getServiceGameFilterList()->getByTagWithDates($tagId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by tag: '.$tagName;
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by tag: '.$tagName;

        return view('games.browse.byTagPage', $bindings);
    }

    public function byDateLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games by release date:';
        $bindings['PageTitle'] = 'Nintendo Switch games by release date:';

        $dateList = $this->getServiceGameCalendar()->getAllowedDates(false);
        $dateListArray = [];

        $allowedYears = $this->getServiceGameCalendar()->getAllowedYears();

        foreach ($allowedYears as $allowedYear) {
            $dateListArray{$allowedYear} = [];
        }

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $this->getServiceGameCalendar()->getStat($dateYear, $dateMonth);
                if ($gameCalendarStat) {
                    $dateCount = $gameCalendarStat->released_count;
                } else {
                    $dateCount = 0;
                }

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

                if (!in_array($dateYear, $allowedYears)) {
                    throw new \Exception('Year '.$dateYear.' could not be found in allowedYears');
                }

                $dateListArray{$dateYear}[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

            }

        }

        $bindings['DateList'] = $dateListArray;
        foreach ($allowedYears as $allowedYear) {
            $bindings['DateList'.$allowedYear] = $dateListArray{$allowedYear};
        }

        return view('games.browse.byDateLanding', $bindings);
    }

    public function byDatePage($dateUrl)
    {
        $dates = $this->getServiceGameCalendar()->getAllowedDates();
        if (!in_array($dateUrl, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($dateUrl);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $gamesByMonthList = $this->getServiceGameCalendar()->getList($calendarYear, $calendarMonth);
        $bindings['GamesByMonthList'] = $gamesByMonthList;

        // Get all dates
        $daysInMonth = $dtDate->format('t');
        $dateFrom = sprintf('%s-%s-01', $calendarYear, $calendarMonth);
        $dateTo = sprintf('%s-%s-%s', $calendarYear, $calendarMonth, $daysInMonth);
        $dateList = $this->createDateRange($dateFrom, $dateTo);

        // Get games by date
        $calendarGames = [];
        foreach ($dateList as $dateListItem) {
            $calendarGames[$dateListItem] = [
                'games' => []
            ];
        }
        foreach ($gamesByMonthList as $game) {
            $gameDate = $game->eu_release_date;
            $calendarGames[$gameDate]['games'][] = $game;
        }
        $bindings['CalendarGamesList'] = $calendarGames;

        $bindings['TopTitle'] = 'Nintendo Switch games by release date: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Nintendo Switch games by release date: '.$dtDateDesc;

        $bindings['CalendarDateDesc'] = $dtDateDesc;
        $bindings['CalendarDateUrl'] = $dateUrl;

        // Top Rated by month
        $yearMonth = $calendarYear.$calendarMonth;
        $bindings['GamesRatingsWithRanks'] = $this->getServiceGameRankYearMonth()->getList($yearMonth, 50);

        return view('games.browse.byDatePage', $bindings);
    }

    /**
     * Returns every date between two dates as an array
     * Source: https://www.codementor.io/tips/1170438972/how-to-get-an-array-of-all-dates-between-two-dates-in-php
     * @param string $startDate the start of the date range
     * @param string $endDate the end of the date range
     * @param string $format DateTime format, default is Y-m-d
     * @return array returns every date between $startDate and $endDate, formatted as "Y-m-d"
     * @throws \Exception
     */
    public function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->add(new \DateInterval('P1D')); // add 1 day so it hits the end of the month

        $interval = new \DateInterval('P1D'); // 1 day
        $dateRange = new \DatePeriod($begin, $interval, $end);

        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }

        return $range;
    }
}
