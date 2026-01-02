<?php

namespace App\Http\Controllers\PublicSite\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\AffiliateCodes\Amazon as AmazonAffiliate;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\Game\AutoDescription;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\GameTag\Repository as GameTagRepository;
use App\Domain\News\Repository as NewsRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;

class GameShowController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private AmazonAffiliate $affiliateAmazon,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private AutoDescription $autoDescription,
        private GameRepository $repoGame,
        private GameDeveloperRepository $repoGameDeveloper,
        private GameListsRepository $repoGameLists,
        private GamePublisherRepository $repoGamePublisher,
        private GameStatsRepository $repoGameStats,
        private GameTagRepository $repoGameTag,
        private NewsRepository $repoNews,
        private QuickReviewRepository $repoQuickReview,
        private ReviewLinkRepository $repoReviewLink,
        private UserGamesCollectionRepository $repoUserGamesCollection,
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

        $console = $gameData->console;
        $consoleName = $gameData->console->name;

        $pageTitle = $gameData->title;
        $topTitle = sprintf('%s reviews | Nintendo %s', $gameData->title, $consoleName);
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage($pageTitle, $console), topTitleOverride: $topTitle)->bindings;

        // Main data
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameReviews'] = $this->repoReviewLink->byGame($gameId);
        $bindings['GameQuickReviewList'] = $this->repoQuickReview->byGameActive($gameId);
        $bindings['GameDevelopers'] = $this->repoGameDeveloper->byGame($gameId);
        $bindings['GamePublishers'] = $this->repoGamePublisher->byGame($gameId);
        $bindings['GameTags'] = $this->repoGameTag->getGameTags($gameId);

        // Affiliates
        $amazon = $this->affiliateAmazon->buildLinksForGame($gameData);

        $bindings['Amazon'] = $amazon;

        /*
        // Amazon
        $tempAmazonUkLink = $gameData['amazon_uk_link'];
        if ($tempAmazonUkLink) {
            $amazonUKId = $this->affiliateAmazon->getUKId();
            $fullAmazonUkLink = $tempAmazonUkLink.'?tag='.$amazonUKId;
            $bindings['FullAmazonUkLink'] = $fullAmazonUkLink;
        }

        // Amazon US id
        $amazonUSId = $this->affiliateAmazon->getUSId();
        $tempAmazonUsLink = $gameData['amazon_us_link'];
        if ($tempAmazonUsLink) {
            if (str_contains($tempAmazonUsLink, '?')) {
                $fullAmazonUsLink = $tempAmazonUsLink.'&tag='.$amazonUSId;
            } else {
                $fullAmazonUsLink = $tempAmazonUsLink.'?tag='.$amazonUSId;
            }
            $bindings['FullAmazonUsLink'] = $fullAmazonUsLink;
            $bindings['AmazonUSLinkType'] = 'product';
        } else {
            // Fallback; go to search page
            $urlTitle = $gameData->title;
            $bindings['FullAmazonUsLink'] = 'https://www.amazon.com/s?k=nintendo+switch+games+'.$urlTitle.'&tag='.$amazonUSId;
            $bindings['AmazonUSLinkType'] = 'search';
        }
        */

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
        $bindings['DSNintendoCoUk'] = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        // News
        $bindings['GameNews'] = $this->repoNews->getByGameId($gameId, 10);

        // Related games
        if ($gameData->category_id) {
            $bindings['CategoryName'] = $gameData->category->name;
            $bindings['RelatedByCategory'] = $this->repoGameLists->relatedByCategory(
                $gameData->console_id, $gameData->category_id, $gameId, 4);
        }
        if ($gameData->series_id) {
            $bindings['SeriesName'] = $gameData->series->series;
            $bindings['RelatedBySeries'] = $this->repoGameLists->relatedBySeries(
                $gameData->console_id, $gameData->series_id, $gameId, 4);
        }
        if ($gameData->collection_id) {
            $bindings['CollectionName'] = $gameData->gameCollection->name;
            $bindings['RelatedByCollection'] = $this->repoGameLists->relatedByCollection(
                $gameData->console_id, $gameData->collection_id, $gameId, 4);
        }

        // Total rank count
        $rankMaximum = $this->repoGameStats->totalRankedByConsole($gameData->console_id);
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
        if ($gameData->game_description) {
            $metaDescription = trim($autoDescription).' '.$gameData->game_description;
        } else {
            $metaDescription = trim($autoDescription);
        }
        $bindings['GameBlurb'] = $autoDescription;
        $bindings['MetaDescription'] = strip_tags($metaDescription);

        $bindings['CanonicalUrl'] = route('game.show', ['id' => $gameId, 'linkTitle' => $gameData->link_title]);

        return view('public.games.page.show', $bindings);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showId($id)
    {
        $gameData = $this->repoGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

}
