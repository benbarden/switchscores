<?php

namespace App\Http\Controllers\Admin;

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


        // Action lists
        $actionListGamesForReleaseCountEu = $serviceGame->getActionListGamesForRelease('eu');
        $actionListGamesForReleaseCountUs = $serviceGame->getActionListGamesForRelease('us');
        $actionListGamesForReleaseCountJp = $serviceGame->getActionListGamesForRelease('jp');
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $unprocessedFeedReviewItems = $feedItemReviewService->getUnprocessed();
        $pendingReviewUser = $reviewUserService->getByStatus(ReviewUser::STATUS_PENDING);
        $pendingPartnerReview = $partnerReviewService->getByStatus(PartnerReview::STATUS_PENDING);

        $bindings['ActionListGamesForReleaseCountEu'] = count($actionListGamesForReleaseCountEu);
        $bindings['ActionListGamesForReleaseCountUs'] = count($actionListGamesForReleaseCountUs);
        $bindings['ActionListGamesForReleaseCountJp'] = count($actionListGamesForReleaseCountJp);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingReviewUserCount'] = count($pendingReviewUser);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);

        // Action lists
        $actionListNintendoUrlNoPackshotCount = $serviceGame->getActionListNintendoUrlNoPackshots($regionCode);
        $actionListRecentNoNintendoUrlCount = $serviceGame->getActionListRecentNoNintendoUrl($regionCode);
        $actionListUpcomingNoNintendoUrlCount = $serviceGame->getActionListUpcomingNoNintendoUrl($regionCode);

        $bindings['ActionListNintendoUrlNoPackshotsCount'] = count($actionListNintendoUrlNoPackshotCount);
        $bindings['ActionListRecentNoNintendoUrlCount'] = count($actionListRecentNoNintendoUrlCount);
        $bindings['ActionListUpcomingNoNintendoUrlCount'] = count($actionListUpcomingNoNintendoUrlCount);


        // Developers and Publishers
        $bindings['NoDeveloperCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['OldDevelopersToMigrate'] = $serviceGameDeveloper->countOldDevelopersToMigrate();
        $bindings['GameDeveloperLinks'] = $serviceGameDeveloper->countGameDeveloperLinks();

        $bindings['NoPublisherCount'] = $serviceGamePublisher->countGamesWithNoPublisher();
        $bindings['OldPublishersToMigrate'] = $serviceGamePublisher->countOldPublishersToMigrate();
        $bindings['GamePublisherLinks'] = $serviceGamePublisher->countGamePublisherLinks();

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
