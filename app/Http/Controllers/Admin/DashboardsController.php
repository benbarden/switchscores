<?php

namespace App\Http\Controllers\Admin;

use App\SiteAlert;
use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use App\ReviewUser;
use App\PartnerReview;

class DashboardsController extends Controller
{
    public function games()
    {
        $pageTitle = 'Games dashboard';

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();

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

        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();

        $bindings = [];

        // Action lists
        $missingTags = $serviceGameList->getGamesWithoutTags();
        $bindings['NoTagCount'] = count($missingTags);
        $missingTypesAndTags = $serviceGameList->getGamesWithoutTypesOrTags();
        $bindings['NoTypeOrTagCount'] = count($missingTypesAndTags);

        // Missing data
        $missingGenres = $serviceGameGenre->getGamesWithoutGenres($regionCode);
        $bindings['MissingGenresCount'] = count($missingGenres);

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
