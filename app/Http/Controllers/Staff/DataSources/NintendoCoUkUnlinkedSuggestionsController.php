<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\DataSource\NintendoCoUk\GameLinker;
use App\Domain\DataSource\NintendoCoUk\SearchUrl;
use App\Domain\DataSource\NintendoCoUk\UnlinkedMatcher;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

/**
 * Unlinked suggestions (#192): unlinked Nintendo.co.uk API items with a likely game to link
 * to, and the rest with links for adding the game by hand. Nothing links without a click,
 * and linking goes through GameLinker, the same code as the Edit link page.
 */
class NintendoCoUkUnlinkedSuggestionsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameRepository $repoGame,
        private UnlinkedMatcher $matcher,
        private GameLinker $gameLinker
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Suggestions';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesNintendoUnlinkedSubpage($pageTitle))->bindings;

        // The same items as the Unlinked page
        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();
        $items = $this->repoDataSourceParsed->getNintendoCoUkUnlinkedWithEUDate($ignoreIdList);

        $suggestions = $this->matcher->suggest($items);
        $bindings['Matches'] = $suggestions['matches'];
        $bindings['Several'] = $suggestions['several'];
        $bindings['NoMatch'] = array_map(
            fn ($row) => $row + ['searchUrl' => SearchUrl::forTitle($row['item']->title, $row['item']->console_id)],
            $suggestions['none']
        );

        return view('staff.data-sources.nintendo-co-uk.unlinked-suggestions', $bindings);
    }

    public function link()
    {
        $request = request();
        $back = redirect()->route('staff.data-sources.nintendo-co-uk.unlinked.suggestions');

        $game = $this->repoGame->find($request->input('game_id'));
        $item = $this->repoDataSourceParsed->getNintendoCoUkByLinkId($request->input('link_id'));
        if (!$game || !$item) {
            return $back->with('unlinkedError', 'Game or API item not found.');
        }

        // The page may be out of date: another tab, or the Edit link page, may have linked either side
        if ($item->game_id) {
            return $back->with('unlinkedError', $item->title.' is already linked to a game.');
        }
        if ($game->eshop_europe_fs_id) {
            return $back->with('unlinkedError', $game->title.' is already linked to an API item.');
        }
        if ($game->console_id != $item->console_id) {
            return $back->with('unlinkedError', $game->title.' is on a different console.');
        }

        $this->gameLinker->link($game, $item);

        return $back->with('unlinkedSuccess', $game->id)->with('unlinkedSuccessTitle', $game->title);
    }
}
