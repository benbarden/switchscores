<?php

namespace App\Http\Controllers\Members;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserWishlist\Repository as UserWishlistRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;

class WishlistController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private UserWishlistRepository $repoWishlist,
        private UserGamesCollectionRepository $repoUserGamesCollection
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Wishlist';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['WishlistItems'] = $this->repoWishlist->byUser($userId);
        $bindings['CollectionGameIds'] = $this->repoUserGamesCollection->byUserGameIds($userId);

        return view('members.wishlist.index', $bindings);
    }

    public function add($gameId)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        // Check if already in wishlist
        if ($this->repoWishlist->isGameInWishlist($userId, $gameId)) {
            return response()->json(['error' => 'Game already in wishlist'], 400);
        }

        $this->repoWishlist->add($userId, $gameId);

        return response()->json(['status' => 'OK', 'message' => 'Added to wishlist']);
    }

    public function remove($gameId)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $this->repoWishlist->deleteByUserAndGame($userId, $gameId);

        return response()->json(['status' => 'OK', 'message' => 'Removed from wishlist']);
    }
}
