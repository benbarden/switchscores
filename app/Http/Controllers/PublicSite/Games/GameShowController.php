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
use App\Models\Console;

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
     * Switch 1 game page (legacy URL format)
     * Redirects Switch 2 games to their new URL format
     */
    public function show($gameId, $linkTitle)
    {
        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->isSoftDeleted()) {
            abort(410, 'This game has been removed.');
        }

        // Redirect Switch 2 games to new URL format
        if ($gameData->console_id === Console::ID_SWITCH_2) {
            return redirect(route('game.show.switch2', [
                'id' => $gameId,
                'linkTitle' => $gameData->link_title
            ]), 301);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        return $this->renderGamePage($gameData);
    }

    /**
     * Switch 2 game page (new URL format)
     * Redirects Switch 1 games to legacy URL format
     */
    public function showSwitch2($gameId, $linkTitle)
    {
        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->isSoftDeleted()) {
            abort(410, 'This game has been removed.');
        }

        // Redirect Switch 1 games to legacy URL format
        if ($gameData->console_id === Console::ID_SWITCH_1) {
            return redirect(route('game.show', [
                'id' => $gameId,
                'linkTitle' => $gameData->link_title
            ]), 301);
        }

        if ($gameData->link_title != $linkTitle) {
            return redirect(route('game.show.switch2', [
                'id' => $gameId,
                'linkTitle' => $gameData->link_title
            ]), 301);
        }

        return $this->renderGamePage($gameData);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * Redirects Switch 2 games to new URL format
     */
    public function showId($id)
    {
        $gameData = $this->repoGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->isSoftDeleted()) {
            abort(410, 'This game has been removed.');
        }

        // Redirect to appropriate URL based on console
        if ($gameData->console_id === Console::ID_SWITCH_2) {
            return redirect(route('game.show.switch2', [
                'id' => $id,
                'linkTitle' => $gameData->link_title
            ]), 301);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

    /**
     * Switch 2 game page redirect from ID only
     */
    public function showIdSwitch2($id)
    {
        $gameData = $this->repoGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->isSoftDeleted()) {
            abort(410, 'This game has been removed.');
        }

        // Redirect Switch 1 games to legacy URL format
        if ($gameData->console_id === Console::ID_SWITCH_1) {
            return redirect(route('game.show', [
                'id' => $id,
                'linkTitle' => $gameData->link_title
            ]), 301);
        }

        return redirect(route('game.show.switch2', [
            'id' => $id,
            'linkTitle' => $gameData->link_title
        ]), 301);
    }

    /**
     * Build the canonical URL for a game based on its console
     */
    private function buildGameUrl($gameData): string
    {
        if ($gameData->console_id === Console::ID_SWITCH_2) {
            return route('game.show.switch2', [
                'id' => $gameData->id,
                'linkTitle' => $gameData->link_title
            ]);
        }

        return route('game.show', [
            'id' => $gameData->id,
            'linkTitle' => $gameData->link_title
        ]);
    }

    /**
     * Render the game page (shared logic for both S1 and S2)
     */
    private function renderGamePage($gameData)
    {
        $bindings = [];
        $gameId = $gameData->id;

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

        // Video
        $videoUrl = $gameData->video_url;
        if ($videoUrl) {
            if (!str_contains($videoUrl, 'https://youtu.be/')) {
                $cleanVideoUrl = $videoUrl;
            } else {
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

        $bindings['CanonicalUrl'] = $this->buildGameUrl($gameData);

        return view('public.games.page.show', $bindings);
    }

}
