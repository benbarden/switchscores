<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\ImageStorageMigrator;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\Repository\GameImageRepository;
use App\Models\Console;

class ImageMigrationController extends Controller
{
    const PER_PAGE = 50;
    const BATCH_SIZE = 50;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameImageRepository $repoGameImage,
        private ImageStorageMigrator $migrator,
    )
    {
    }

    public function show(Request $request)
    {
        $pageTitle = 'To be migrated';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesImagesSubpage($pageTitle))->bindings;

        $consoleId = $this->consoleFilter($request);

        $bindings['Unmigrated'] = $this->repoGameImage->paginateUnmigrated($consoleId, self::PER_PAGE);
        $bindings['ConsoleFilter'] = $consoleId;
        $bindings['ConsoleOptions'] = [
            Console::ID_SWITCH_1 => Console::DESC_SWITCH_1,
            Console::ID_SWITCH_2 => Console::DESC_SWITCH_2,
        ];
        $bindings['BatchSize'] = self::BATCH_SIZE;
        $bindings['PackshotsConfigured'] = $this->packshotsConfigured();

        return view('staff.games.images.to-migrate', $bindings);
    }

    public function recent()
    {
        $pageTitle = 'Recently migrated';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesImagesSubpage($pageTitle))->bindings;

        $bindings['RecentlyMigrated'] = $this->repoGameImage->paginateRecentlyMigrated(self::PER_PAGE);
        $bindings['PackshotsConfigured'] = $this->packshotsConfigured();

        return view('staff.games.images.recent', $bindings);
    }

    /** Read + validate the console query filter (null = all). */
    private function consoleFilter(Request $request): ?int
    {
        $consoleId = (int) $request->query('console');

        return in_array($consoleId, [Console::ID_SWITCH_1, Console::ID_SWITCH_2], true) ? $consoleId : null;
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

        return back()->with('success', "\"{$game->title}\" packshots moved to object storage.");
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

    /** Migrate the next batch of oldest unmigrated games (respects the console filter). */
    public function migrateBatch(Request $request)
    {
        if (!$this->packshotsConfigured()) {
            return back()->with('error', 'Packshots storage is not configured (PACKSHOTS_* env). Migration is disabled here.');
        }

        $consoleId = $this->consoleFilter($request);
        $games = $this->repoGameImage->nextUnmigratedBatch($consoleId, self::BATCH_SIZE);

        if ($games->isEmpty()) {
            return back()->with('error', 'No unmigrated games left for this filter.');
        }

        foreach ($games as $game) {
            $this->migrator->migrate($game);
            $this->repoGame->clearCacheCoreData($game->id);
        }

        return back()->with('success', $games->count() . ' games migrated to object storage.');
    }
}
