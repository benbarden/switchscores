<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\News\Repository as NewsRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Domain\Game\AutoDescription;
use App\Domain\AffiliateCodes\Amazon as AmazonAffiliate;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;

class GameShowController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameRepository $repoGame,
        private FeaturedGameRepository $repoFeaturedGames,
        private GameListsRepository $repoGameLists,
        private GameStatsRepository $repoGameStats,
        private NewsRepository $repoNews,
        private Breadcrumbs $viewBreadcrumbs,
        private GamePublisherRepository $repoGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper,
        private AutoDescription $autoDescription,
        private UserGamesCollectionRepository $repoUserGamesCollection,
        private AmazonAffiliate $affiliateAmazon
    )
    {
    }

    /**
     * @param $gameId
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($gameId, $linkTitle)
    {
        $bindings = [];

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage($gameData->title);

        // Main data
        $bindings['TopTitle'] = $gameData->title.' Nintendo Switch reviews';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameReviews'] = $this->getServiceReviewLink()->getByGame($gameId);
        $bindings['GameQuickReviewList'] = $this->getServiceQuickReview()->getActiveByGame($gameId);
        $bindings['GameDevelopers'] = $this->repoGameDeveloper->byGame($gameId);
        $bindings['GamePublishers'] = $this->repoGamePublisher->byGame($gameId);
        $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);

        // Amazon
        $tempAmazonUkLink = $gameData['amazon_uk_link'];
        if ($tempAmazonUkLink) {
            $amazonUKId = $this->affiliateAmazon->getUKId();
            $fullAmazonUkLink = $tempAmazonUkLink.'?tag='.$amazonUKId;
            $bindings['FullAmazonUkLink'] = $fullAmazonUkLink;
        }

        // Amazon US id
        $amazonUSId = $this->affiliateAmazon->getUSId();
        $urlTitle = $gameData->title;
        $bindings['FullAmazonUsLink'] = 'https://www.amazon.com/s?k=nintendo+switch+games+'.$urlTitle.'&tag='.$amazonUSId;

        // Video
        $videoUrl = $gameData->video_url;
        if ($videoUrl) {
            if (strpos($videoUrl, 'https://youtu.be/') === false) {
                // Standard URL
                $cleanVideoUrl = $videoUrl;
            } else {
                // Shortened URL
                $videoData = explode('https://youtu.be/', $videoUrl);
                if (count($videoData) <> 2) {
                    $cleanVideoUrl = null;
                }
                $cleanVideoUrl = 'https://www.youtube.com/watch?v='.$videoData[1];
            }
            if ($cleanVideoUrl != null) {
                $bindings['CleanVideoUrl'] = $cleanVideoUrl;
            }
        }

        // Data sources
        $bindings['DSNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        // News
        $bindings['GameNews'] = $this->repoNews->getByGameId($gameId, 10);

        // Related games
        if ($gameData->category_id) {
            $bindings['CategoryName'] = $gameData->category->name;
            $bindings['RelatedByCategory'] = $this->repoGameLists->relatedByCategory($gameData->category_id, $gameId, 6);
        }
        if ($gameData->series_id) {
            $bindings['SeriesName'] = $gameData->series->series;
            $bindings['RelatedBySeries'] = $this->repoGameLists->relatedBySeries($gameData->series_id, $gameId, 6);
        }
        if ($gameData->collection_id) {
            $bindings['CollectionName'] = $gameData->gameCollection->name;
            $bindings['RelatedByCollection'] = $this->repoGameLists->relatedByCollection($gameData->collection_id, $gameId, 6);
        }

        // Total rank count
        $rankMaximum = $this->repoGameStats->totalRanked();
        $bindings['RankMaximum'] = $rankMaximum;

        // Top %
        if ($gameData->game_rank && $rankMaximum) {
            if ($gameData->game_rank <= 100) {
                $bindings['TopRatedItem'] = 'Top 100';
            } else {
                $topPercent = ($gameData->game_rank / $rankMaximum) * 100;
                if ($topPercent <= 50) {
                    $topPercent = round($topPercent, 2);
                    if ($topPercent > 1) {
                        $topPercent = number_format($topPercent, 0);
                    } else {
                        $topPercent = number_format($topPercent, 2);
                    }
                    $bindings['TopPercent'] = $topPercent;
                }
            }
        }

        // Logged in user data
        $currentUser = resolve('User/Repository')->currentUser();
        if ($currentUser) {
            $userId = $currentUser->id;
            $bindings['UserCollectionItem'] = $this->repoUserGamesCollection->byUserGameItem($userId, $gameId);
            $bindings['UserCollectionGame'] = $gameData;
        }

        // Game blurb
        $autoDescription = $this->autoDescription->generate($gameData);
        $bindings['GameBlurb'] = $autoDescription;
        $bindings['MetaDescription'] = strip_tags($autoDescription);

        return view('public.games.page.show', $bindings);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showId($id)
    {
        $serviceGame = $this->repoGame;

        $gameData = $serviceGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

}
