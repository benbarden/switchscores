<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository\GameImageRepository;
use App\Models\Console;

class GameImagesDashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameImageRepository $repoGameImage,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Game images';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesImagesDashboard())->bindings;

        $totalWithImages = $this->repoGameImage->countGamesWithImages();
        $totalInSpaces = $this->repoGameImage->countInSpaces();
        $totalLegacy = max(0, $totalWithImages - $totalInSpaces);

        // Game image stats section
        $bindings['TotalWithImages'] = $totalWithImages;
        $bindings['TotalWithoutImages'] = $this->repoGameImage->countGamesWithoutImages();
        // Orphaned images = storage objects/files with no referencing game. Requires a
        // bucket + disk listing scan (req #5), not a cheap count; deferred (null placeholder).
        $bindings['OrphanedImages'] = null;

        // Migration to CDN section
        $bindings['TotalInSpaces'] = $totalInSpaces;
        $bindings['TotalLegacy'] = $totalLegacy;
        $bindings['PercentInSpaces'] = $this->percent($totalInSpaces, $totalWithImages);

        $bindings['ConsoleBreakdown'] = $this->consoleBreakdown();

        return view('staff.games.images.dashboard', $bindings);
    }

    private function consoleBreakdown(): array
    {
        $withImages = $this->repoGameImage->withImagesByConsole();
        $inSpaces = $this->repoGameImage->inSpacesByConsole();

        $consoles = [
            Console::ID_SWITCH_1 => Console::DESC_SWITCH_1,
            Console::ID_SWITCH_2 => Console::DESC_SWITCH_2,
        ];

        $rows = [];
        foreach ($consoles as $consoleId => $name) {
            $total = $withImages[$consoleId] ?? 0;
            $spaces = $inSpaces[$consoleId] ?? 0;
            $rows[] = [
                'name' => $name,
                'total' => $total,
                'spaces' => $spaces,
                'legacy' => max(0, $total - $spaces),
                'percent' => $this->percent($spaces, $total),
            ];
        }

        return $rows;
    }

    private function percent(int $part, int $whole): float
    {
        if ($whole <= 0) {
            return 0.0;
        }

        return round($part / $whole * 100, 1);
    }
}
