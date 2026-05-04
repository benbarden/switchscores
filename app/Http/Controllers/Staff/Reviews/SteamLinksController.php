<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\Steam\Repository as SteamRepository;
use App\Domain\Steam\SyncService as SteamSyncService;

use App\Enums\SteamStatus;
use App\Models\Game;

class SteamLinksController extends Controller
{
    private const VALID_TABS = ['not-checked', 'not-checked-oldest', 'linked', 'not-on-steam'];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private SteamRepository $repoSteam,
        private SteamSyncService $steamSync
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Steam links';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        $bindings['GamesNotChecked']       = $this->repoSteam->getByStatus(SteamStatus::NOT_CHECKED, 100);
        $bindings['GamesNotCheckedOldest'] = $this->repoSteam->getByStatus(SteamStatus::NOT_CHECKED, 100, true);
        $bindings['GamesLinked']           = $this->repoSteam->getByStatus(SteamStatus::LINKED);
        $bindings['GamesNotOnSteam']       = $this->repoSteam->getByStatus(SteamStatus::NOT_ON_STEAM);

        $bindings['CountNotChecked']  = $this->repoSteam->countByStatus(SteamStatus::NOT_CHECKED);
        $bindings['CountLinked']      = count($bindings['GamesLinked']);
        $bindings['CountNotOnSteam']  = count($bindings['GamesNotOnSteam']);

        return view('staff.reviews.steam-links.index', $bindings);
    }

    public function link(Request $request, int $gameId)
    {
        $request->validate(['steam_id' => 'required|string|max:20']);

        $game = Game::findOrFail($gameId);
        $steamId = $request->input('steam_id');

        $game->steam_id = $steamId;
        $game->steam_status = SteamStatus::LINKED;
        $game->save();

        $result = $this->steamSync->syncGame($game->id, $steamId);

        $message = '"' . $game->title . '" linked to Steam ID ' . $steamId . '.';
        if ($result) {
            $message .= ' Reviews fetched: ' . $result->review_score_desc
                . ' (' . number_format($result->total_reviews) . ' reviews).';
        }

        return $this->redirectToTab($request, 'not-checked')
            ->with('success', $message);
    }

    public function notOnSteam(Request $request, int $gameId)
    {
        $game = Game::findOrFail($gameId);
        $game->steam_id = null;
        $game->steam_status = SteamStatus::NOT_ON_STEAM;
        $game->save();

        return $this->redirectToTab($request, 'not-on-steam')
            ->with('success', '"' . $game->title . '" marked as not on Steam.');
    }

    public function reset(Request $request, int $gameId)
    {
        $game = Game::findOrFail($gameId);
        $game->steam_id = null;
        $game->steam_status = SteamStatus::NOT_CHECKED;
        $game->save();

        return $this->redirectToTab($request, 'not-checked')
            ->with('success', '"' . $game->title . '" reset to not checked.');
    }

    private function redirectToTab(Request $request, string $default)
    {
        $tab = $request->input('redirect_tab', $default);
        if (!in_array($tab, self::VALID_TABS)) {
            $tab = $default;
        }
        return redirect(route('staff.reviews.steam-links.index') . '?tab=' . $tab);
    }
}
