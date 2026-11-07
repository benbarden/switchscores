<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Models\Console;
use App\Domain\AffiliateCodes\Amazon;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDb;
use App\Domain\ReviewLink\DbQueries as ReviewLinkDb;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\Console\Repository as ConsoleRepository;

class ListsController extends Controller
{
    const BUYERS_GUIDE_REGIONS = ['us' => 'US', 'uk' => 'UK'];

    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists,
        private GameListsDb $dbGameLists,
        private ReviewLinkDb $dbReviewLink,
        private ReviewLinkRepository $repoReviewLink,
        private ConsoleRepository $repoConsole,
        private Amazon $affiliateAmazon,
    )
    {
    }

    public function gamesOnSale()
    {
        $console = $this->repoConsole->find(Console::ID_SWITCH_1);
        if (!$console) abort(404);

        $pageTitle = 'Nintendo Switch games currently on sale in Europe';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage($pageTitle, $console))->bindings;

        $bindings['GoodRanks'] = $this->dbGameLists->onSaleGoodRanks(200);
        $bindings['HighestDiscounts'] = $this->dbGameLists->onSaleHighestDiscounts(200);
        $bindings['UnrankedDiscounts'] = $this->dbGameLists->onSaleUnranked(200);

        $bindings['TopRatedSort'] = "[8, 'desc'], [4, 'desc']";
        $bindings['HighestDiscountsSort'] = "[4, 'desc'], [8, 'desc']";
        $bindings['UnrankedDiscountsSort'] = "[6, 'desc'], [3, 'desc']";

        return view('public.lists.list-games-on-sale', $bindings);
    }

    public function recentReviews()
    {
        $pageTitle = 'Recent reviews';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        $bindings['ReviewList'] = $this->repoReviewLink->recentNaturalOrder(35);

        return view('public.lists.list-recent-reviews', $bindings);
    }

    public function recentlyRanked()
    {
        $pageTitle = 'Recently ranked';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        $highlightsRecentlyRanked = $this->dbReviewLink->recentlyRanked(45);

        $bindings['HighlightsRecentlyRanked'] = $highlightsRecentlyRanked;

        return view('public.lists.list-recently-ranked', $bindings);
    }

    /**
     * Asks visitors to pick a region, so nobody lands on the other country's Amazon links.
     */
    public function buyersGuideLanding()
    {
        $pageTitle = 'Nintendo Switch buyer\'s guide';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        $bindings = array_merge($bindings, $this->buyersGuideUpdated());

        return view('public.lists.buyers-guide.landing', $bindings);
    }

    /**
     * The guide for one region. Content comes from config/buyers-guide.php, and every Amazon link
     * carries the guide's own tracking ID so Amazon's reports show what it earns.
     */
    public function buyersGuide(string $region)
    {
        $regionName = self::BUYERS_GUIDE_REGIONS[$region];

        $pageTitle = 'Nintendo Switch buyer\'s guide '.config('buyers-guide.year').' ('.$regionName.')';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::buyersGuideRegion($regionName))->bindings;

        $amazon = $this->affiliateAmazon->forBuyersGuide();

        $hardware = [];
        foreach (config('buyers-guide.hardware', []) as $item) {
            $asin = $item['asin_'.$region] ?? null;
            if (!$asin) {
                continue;
            }
            $hardware[] = [
                'title' => $item['title'],
                'note' => $item['note'] ?? null,
                'url' => $amazon->productLink($region, $asin),
            ];
        }

        $bindings = array_merge($bindings, $this->buyersGuideUpdated());
        $bindings['Region'] = $region;
        $bindings['RegionName'] = $regionName;
        $bindings['OtherRegion'] = $region === 'uk' ? 'us' : 'uk';
        $bindings['OtherRegionName'] = self::BUYERS_GUIDE_REGIONS[$bindings['OtherRegion']];
        $bindings['Hardware'] = $hardware;
        $bindings['Switch2Games'] = $this->buyersGuideGames(config('buyers-guide.games.switch-2', []), $region, $amazon);
        $bindings['Switch1Games'] = $this->buyersGuideGames(config('buyers-guide.games.switch-1', []), $region, $amazon);

        return view('public.lists.buyers-guide.region', $bindings);
    }

    /**
     * The "Updated 7 November 2026 for Black Friday and the holidays." line, from the config.
     */
    private function buyersGuideUpdated(): array
    {
        $updated = config('buyers-guide.updated');

        return [
            'UpdatedDate' => $updated ? \Carbon\Carbon::parse($updated)->format('j F Y') : null,
            'UpdatedNote' => config('buyers-guide.updated_note'),
        ];
    }

    /**
     * The picked games in the order listed, each with its buy link for the region. UK links are
     * product links only, as Amazon UK doesn't pay for searches.
     */
    private function buyersGuideGames(array $gameIds, string $region, Amazon $amazon): array
    {
        $order = array_flip($gameIds);
        $games = $this->repoGameLists->byIdList($gameIds)
            ->sortBy(fn($game) => $order[$game->id])
            ->values();

        $items = [];
        foreach ($games as $game) {
            $links = $amazon->buildLinksForGame($game);
            if ($region === 'uk') {
                $buyUrl = $links->urlUk;
                $buyLabel = 'Buy at Amazon UK';
            } else {
                $buyUrl = $links->urlUs;
                $buyLabel = $links->usType === 'search' ? 'Find at Amazon US' : 'Buy at Amazon US';
            }
            $items[] = [
                'game' => $game,
                'buyUrl' => $game->isDelisted() ? null : $buyUrl,
                'buyLabel' => $buyLabel,
            ];
        }

        return $items;
    }

}
