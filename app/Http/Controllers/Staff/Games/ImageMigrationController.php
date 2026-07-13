<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\ImageStorageMigrator;
use App\Domain\Game\Repository as GameRepository;
use App\Models\Console;

class ImageMigrationController extends Controller
{
    const POC_LIMIT = 20;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private ImageStorageMigrator $migrator,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Image storage migration';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['Games'] = $this->repoGame->getByConsoleLowestIdsWithImages(Console::ID_SWITCH_2, self::POC_LIMIT);
        $bindings['PocLimit'] = self::POC_LIMIT;
        $bindings['PackshotsConfigured'] = $this->packshotsConfigured();

        return view('staff.games.image-migration', $bindings);
    }

    /**
     * Is the packshots storage disk actually configured? Guards prod, where the page
     * is live but PACKSHOTS_* is unset — migrating there would flip a game to `spaces`
     * with no real object and break its image on the public site.
     */
    private function packshotsConfigured(): bool
    {
        $disk = config('filesystems.disks.packshots');

        return !empty($disk['bucket']) && !empty($disk['key']);
    }

    public function migrate($gameId)
    {
        if (!$this->packshotsConfigured()) {
            return back()->with('error', 'Packshots storage is not configured (PACKSHOTS_* env). Migration is disabled here.');
        }

        $game = $this->repoGame->findWithoutCache($gameId);
        if (!$game) {
            return back()->with('error', "Game {$gameId} not found.");
        }

        $this->migrator->migrate($game);
        $this->repoGame->clearCacheCoreData($gameId);

        return back()->with('success', "\"{$game->title}\" packshots moved to Spaces.");
    }

    public function revert($gameId)
    {
        if (!$this->packshotsConfigured()) {
            return back()->with('error', 'Packshots storage is not configured (PACKSHOTS_* env). Revert is disabled here.');
        }

        $game = $this->repoGame->findWithoutCache($gameId);
        if (!$game) {
            return back()->with('error', "Game {$gameId} not found.");
        }

        $this->migrator->revert($game);
        $this->repoGame->clearCacheCoreData($gameId);

        return back()->with('success', "\"{$game->title}\" packshots moved back to legacy.");
    }
}
