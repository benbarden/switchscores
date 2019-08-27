<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

use App\SiteAlert;
use App\ReviewUser;
use App\PartnerReview;

use App\Services\AdminDashboards\CategorisationService;

class DashboardsController extends Controller
{
    public function games()
    {
        $pageTitle = 'Games dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        // Games to release
        $actionListGamesForReleaseCountEu = $serviceGame->getActionListGamesForRelease('eu');
        $actionListGamesForReleaseCountUs = $serviceGame->getActionListGamesForRelease('us');
        $actionListGamesForReleaseCountJp = $serviceGame->getActionListGamesForRelease('jp');
        $bindings['GamesForReleaseCountEu'] = count($actionListGamesForReleaseCountEu);
        $bindings['GamesForReleaseCountUs'] = count($actionListGamesForReleaseCountUs);
        $bindings['GamesForReleaseCountJp'] = count($actionListGamesForReleaseCountJp);

        // Missing data
        $missingVideoUrl = $serviceGame->getByNullField('video_url', $regionCode);
        $missingAmazonUkLink = $serviceGame->getWithoutAmazonUkLink();
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);

        // Stats
        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('admin.dashboards.games', $bindings);
    }

    public function reviews()
    {
        $pageTitle = 'Reviews dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceFeedItemReview = $serviceContainer->getFeedItemReviewService();
        $servicePartnerReview = $serviceContainer->getPartnerReviewService();
        $serviceReviewUser = $serviceContainer->getReviewUserService();

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceGameRankAllTime = $serviceContainer->getGameRankAllTimeService();
        $serviceTopRated = $serviceContainer->getTopRatedService();

        $bindings = [];

        // Action lists
        $unprocessedFeedReviewItems = $serviceFeedItemReview->getUnprocessed();
        $pendingPartnerReview = $servicePartnerReview->getByStatus(PartnerReview::STATUS_PENDING);
        $pendingReviewUser = $serviceReviewUser->getByStatus(ReviewUser::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);
        $bindings['PendingReviewUserCount'] = count($pendingReviewUser);

        // Information
        $bindings['ReviewLinkCount'] = $serviceReviewLinks->countActive();
        $bindings['RankedGameCount'] = $serviceGameRankAllTime->countRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('admin.dashboards.reviews', $bindings);
    }

    public function categorisation()
    {
        $pageTitle = 'Categorisation dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameFilterList = $serviceContainer->getGameFilterListService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameSeries = $serviceContainer->getGameSeriesService();
        $serviceTag = $serviceContainer->getTagService();
        $serviceGameTag = $serviceContainer->getGameTagService();

        $serviceCategorisation = new CategorisationService();

        $bindings = [];

        // Used in several calculations below
        $totalGameCount = $serviceGame->getCount();

        // Game stats: Primary type
        $statsWithPrimaryType = $serviceCategorisation->countGamesWithPrimaryType();
        $statsWithoutPrimaryType = $serviceCategorisation->countGamesWithoutPrimaryType();
        $bindings['StatsWithPrimaryType'] = $statsWithPrimaryType;
        $bindings['StatsWithoutPrimaryType'] = $statsWithoutPrimaryType;
        $statsPrimaryTypeProgress = ($statsWithPrimaryType) / $totalGameCount * 100;
        $bindings['StatsPrimaryTypeProgress'] = round($statsPrimaryTypeProgress, 2);

        // Game stats: Tags
        $missingTags = $serviceGameFilterList->getGamesWithoutTags();
        $statsWithoutTags = count($missingTags);
        $statsWithTags = $totalGameCount - $statsWithoutTags;
        $bindings['StatsWithoutTags'] = $statsWithoutTags;
        $bindings['StatsWithTags'] = $statsWithTags;
        $statsTagsProgress = ($statsWithTags) / $totalGameCount * 100;
        $bindings['StatsTagsProgress'] = round($statsTagsProgress, 2);

        // Game stats: Series
        $statsWithSeries = $serviceCategorisation->countGamesWithSeries();
        $statsWithoutSeries = $serviceCategorisation->countGamesWithoutSeries();
        $bindings['StatsWithSeries'] = $statsWithSeries;
        $bindings['StatsWithoutSeries'] = $statsWithoutSeries;

        // Genres
        $missingGenres = $serviceGameGenre->getGamesWithoutGenres($regionCode);
        $bindings['MissingGenresCount'] = count($missingGenres);

        // No type or tag
        $missingTypesAndTags = $serviceGameFilterList->getGamesWithoutTypesOrTags();
        $bindings['NoTypeOrTagCount'] = count($missingTypesAndTags);

        // Migrations: Genre, no primary type
        $statsGenresNoPrimaryType = $serviceGameGenre->getGamesWithGenresNoPrimaryType($regionCode);
        $statsCountGenresNoPrimaryType = count($statsGenresNoPrimaryType);
        $bindings['StatsCountGenresNoPrimaryType'] = $statsCountGenresNoPrimaryType;

        // Title matches: Series
        $seriesList = $serviceGameSeries->getAll();

        $seriesArray = [];

        foreach ($seriesList as $series) {

            $seriesId = $series->id;
            $seriesName = $series->series;
            $seriesLink = $series->link_title;

            $gameSeriesList = $serviceGame->getSeriesTitleMatch($seriesName);
            $gameCount = count($gameSeriesList);

            if ($gameCount > 0) {

                $seriesArray[] = [
                    'id' => $seriesId,
                    'name' => $seriesName,
                    'link' => $seriesLink,
                    'gameCount' => count($gameSeriesList),
                ];

            }

        }

        $bindings['GameSeriesMatchList'] = $seriesArray;

        // Title matches: Tags
        $tagList = $serviceTag->getAll();

        $tagArray = [];

        foreach ($tagList as $tag) {

            $tagId = $tag->id;
            $tagName = $tag->tag_name;
            $tagLink = $tag->link_title;

            $gameTagList = $serviceGame->getTagTitleMatch($tagName);

            if ($gameTagList) {

                $gameTagCount = 0;
                foreach ($gameTagList as $game) {
                    if (!$serviceGameTag->gameHasTag($game->id, $tagId)) {
                        $gameTagCount++;
                    }
                }

                if ($gameTagCount > 0) {

                    $tagArray[] = [
                        'id' => $tagId,
                        'name' => $tagName,
                        'link' => $tagLink,
                        'gameCount' => $gameTagCount,
                    ];

                }

            }

        }

        $bindings['GameTagMatchList'] = $tagArray;

        // Core stuff
        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('admin.dashboards.categorisation', $bindings);
    }

    public function partners()
    {
        $pageTitle = 'Partners dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['DeveloperMissingCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['NewDeveloperToSetCount'] = $serviceGameDeveloper->countNewDevelopersToSet();
        $bindings['OldDeveloperToClearCount'] = $serviceGameDeveloper->countOldDevelopersToClear();
        $bindings['PublisherMissingCount'] = $serviceGamePublisher->countGamesWithNoPublisher();
        $bindings['NewPublisherToSetCount'] = $serviceGamePublisher->countNewPublishersToSet();
        $bindings['OldPublisherToClearCount'] = $serviceGamePublisher->countOldPublishersToClear();

        // Stats
        $bindings['GameDeveloperLinks'] = $serviceGameDeveloper->countGameDeveloperLinks();
        $bindings['GamePublisherLinks'] = $serviceGamePublisher->countGamePublisherLinks();

        return view('admin.dashboards.partners', $bindings);
    }

    public function eshop()
    {
        $pageTitle = 'eShop dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['NoPriceCount'] = $serviceGame->countWithoutPrices();
        $bindings['EshopEuropeTotalCount'] = $serviceEshopEurope->getTotalCount();
        $bindings['EshopEuropeLinkedCount'] = $serviceEshopEurope->getAllWithLink(null, true);
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);

        return view('admin.dashboards.eshop', $bindings);
    }

    public function stats()
    {
        $pageTitle = 'Stats dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);

        return view('admin.dashboards.stats', $bindings);
    }

    public function feedItemsLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PageTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function index()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceUser = $serviceContainer->getUserService();

        $feedItemGameService = $serviceContainer->getFeedItemGameService();
        $serviceFeedItemReview = $serviceContainer->getFeedItemReviewService();

        $serviceSiteAlert = $serviceContainer->getSiteAlertService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();

        $bindings = [];

        $pageTitle = 'Admin dashboard';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        // Approvals
        $serviceMarioMakerLevels = $serviceContainer->getMarioMakerLevelService();
        $bindings['MarioMakerLevelPendingCount'] = $serviceMarioMakerLevels->getPending()->count();

        // Information and site stats
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);

        // Updates requiring approval
        $unprocessedFeedReviewItems = $serviceFeedItemReview->getUnprocessed();
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);

        // Action lists
        $bindings['SiteAlertErrorCount'] = $serviceSiteAlert->countByType(SiteAlert::TYPE_ERROR);
        $bindings['SiteAlertLatest'] = $serviceSiteAlert->getLatest(SiteAlert::TYPE_ERROR);

        return view('admin.dashboards.index', $bindings);
    }
}
