<?php

namespace App\Http\Controllers\Admin;

use App\SiteAlert;
use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use App\ReviewUser;
use App\PartnerReview;

class IndexController extends Controller
{
    public function feedItemsLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PageTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceUser = $serviceContainer->getUserService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $reviewUserService = $serviceContainer->getReviewUserService();
        $partnerReviewService = $serviceContainer->getPartnerReviewService();
        $feedItemGameService = $serviceContainer->getFeedItemGameService();
        $feedItemReviewService = $serviceContainer->getFeedItemReviewService();
        $gameTagService = $serviceContainer->getGameTagService();
        $gameGenreService = $serviceContainer->getGameGenreService();
        $serviceGame = $serviceContainer->getGameService();

        $serviceSiteAlert = $serviceContainer->getSiteAlertService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PageTitle'] = 'Admin index';


        // Information and site stats
        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);
        $bindings['RankedGameCount'] = $serviceTopRated->getCount($regionCode);
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);
        $bindings['ReviewLinkCount'] = $serviceReviewLinks->countActive();
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();
        $bindings['EshopEuropeTotalCount'] = $serviceEshopEurope->getTotalCount();
        $bindings['EshopEuropeLinkedCount'] = $serviceEshopEurope->getAllWithLink(null, true);
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);
        $bindings['GameDeveloperLinks'] = $serviceGameDeveloper->countGameDeveloperLinks();
        $bindings['GamePublisherLinks'] = $serviceGamePublisher->countGamePublisherLinks();


        // Updates requiring approval
        $unprocessedFeedReviewItems = $feedItemReviewService->getUnprocessed();
        $pendingPartnerReview = $partnerReviewService->getByStatus(PartnerReview::STATUS_PENDING);
        $pendingReviewUser = $reviewUserService->getByStatus(ReviewUser::STATUS_PENDING);
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);
        $bindings['PendingReviewUserCount'] = count($pendingReviewUser);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);


        // Games to release
        $actionListGamesForReleaseCountEu = $serviceGame->getActionListGamesForRelease('eu');
        $actionListGamesForReleaseCountUs = $serviceGame->getActionListGamesForRelease('us');
        $actionListGamesForReleaseCountJp = $serviceGame->getActionListGamesForRelease('jp');
        $bindings['ActionListGamesForReleaseCountEu'] = count($actionListGamesForReleaseCountEu);
        $bindings['ActionListGamesForReleaseCountUs'] = count($actionListGamesForReleaseCountUs);
        $bindings['ActionListGamesForReleaseCountJp'] = count($actionListGamesForReleaseCountJp);


        // Action lists
        $bindings['SiteAlertErrorCount'] = $serviceSiteAlert->countByType(SiteAlert::TYPE_ERROR);
        $bindings['NoPriceCount'] = $serviceGame->countWithoutPrices();
        $bindings['DeveloperMissingCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['NewDeveloperToSetCount'] = $serviceGameDeveloper->countNewDevelopersToSet();
        $bindings['OldDeveloperToClearCount'] = $serviceGameDeveloper->countOldDevelopersToClear();
        $bindings['PublisherMissingCount'] = $serviceGamePublisher->countGamesWithNoPublisher();
        $bindings['NewPublisherToSetCount'] = $serviceGamePublisher->countNewPublishersToSet();
        $bindings['OldPublisherToClearCount'] = $serviceGamePublisher->countOldPublishersToClear();


        // Action lists (to be replaced)
        $actionListNintendoUrlNoPackshotCount = $serviceGame->getActionListNintendoUrlNoPackshots($regionCode);
        $actionListRecentNoNintendoUrlCount = $serviceGame->getActionListRecentNoNintendoUrl($regionCode);
        $actionListUpcomingNoNintendoUrlCount = $serviceGame->getActionListUpcomingNoNintendoUrl($regionCode);
        $bindings['ActionListNintendoUrlNoPackshotsCount'] = count($actionListNintendoUrlNoPackshotCount);
        $bindings['ActionListRecentNoNintendoUrlCount'] = count($actionListRecentNoNintendoUrlCount);
        $bindings['ActionListUpcomingNoNintendoUrlCount'] = count($actionListUpcomingNoNintendoUrlCount);


        // Missing data - others
        $missingTags = $gameTagService->getGamesWithoutTags($regionCode);
        $missingGenres = $gameGenreService->getGamesWithoutGenres($regionCode);
        $missingVideoUrl = $serviceGame->getByNullField('video_url', $regionCode);
        $missingAmazonUkLink = $serviceGame->getWithoutAmazonUkLink();

        $bindings['MissingTagsCount'] = count($missingTags);
        $bindings['MissingGenresCount'] = count($missingGenres);
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);


        return view('admin.index', $bindings);
    }
}
