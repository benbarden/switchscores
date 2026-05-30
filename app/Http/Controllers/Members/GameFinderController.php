<?php

namespace App\Http\Controllers\Members;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Console\Repository as ConsoleRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Domain\UserWishlist\Repository as UserWishlistRepository;
use App\Domain\UserIgnoredGames\Repository as UserIgnoredGamesRepository;

class GameFinderController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory,
        private ConsoleRepository $repoConsole,
        private GameRepository $repoGame,
        private UserGamesCollectionRepository $repoUserGamesCollection,
        private UserWishlistRepository $repoWishlist,
        private UserIgnoredGamesRepository $repoIgnoredGames
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Find me a game';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $request = request();
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        // Get filter options - hierarchical categories
        $bindings['Categories'] = $this->repoCategory->topLevelCategories();
        $bindings['ConsoleList'] = $this->repoConsole->consoleList();
        $bindings['CollectionGameIds'] = $this->repoUserGamesCollection->byUserGameIds($userId);
        $bindings['WishlistGameIds'] = $this->repoWishlist->byUserGameIds($userId);
        $ignoredGameIds = $this->repoIgnoredGames->byUserGameIds($userId);
        $bindings['IgnoredGameIds'] = $ignoredGameIds;
        $bindings['IgnoredCount'] = count($ignoredGameIds);

        // If search submitted
        if ($request->has('search')) {
            $filters = [
                'keywords' => $request->input('keywords'),
                'category_id' => $request->input('category_id'),
                'console_id' => $request->input('console_id'),
                'min_rating' => $request->input('min_rating'),
                'ranked_only' => $request->input('ranked_only', '1'), // Default to ranked only
                'has_local_multiplayer' => $request->input('has_local_multiplayer'),
                'has_online_play' => $request->input('has_online_play'),
                'min_players' => $request->input('min_players'),
                'play_mode_tv' => $request->input('play_mode_tv'),
                'play_mode_tabletop' => $request->input('play_mode_tabletop'),
                'play_mode_handheld' => $request->input('play_mode_handheld'),
                'exclude_owned' => $request->input('exclude_owned'),
                'exclude_ignored' => true,
                'ignored_game_ids' => $ignoredGameIds->toArray(),
            ];

            $bindings['Filters'] = $filters;
            $bindings['SearchResults'] = $this->repoGame->findWithFilters($filters, $userId);
        } else {
            // Default filters for initial page load
            $bindings['Filters'] = [
                'ranked_only' => '1',
            ];
        }

        return view('members.game-finder.index', $bindings);
    }
}
