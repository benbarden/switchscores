<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\Repository\GameImageRepository;
use App\Models\Console;
use App\Models\GameImage;
use App\Services\Game\Images as GameImagesService;
use Illuminate\Support\Facades\Storage;

class GameImagesDashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameImageRepository $repoGameImage,
        private GameRepository $repoGame,
        private ImageResolver $resolver,
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

    /**
     * Per-game packshot inspector.
     *
     * Every image problem so far has been the same question - does what the DB says match what
     * is actually stored? - and answering it has meant running SQL and checking the filesystem
     * by hand (the missing-header games, Snipperclips Plus, the dead-filename sweep). This puts
     * both sides on one screen: the game_images row, the legacy columns, and whether the file
     * or object each one names actually exists.
     *
     * It is also the verification screen Phase 2 needs. Deleting legacy files one by one
     * requires confirming the Spaces object is really there first, and until now there was no
     * UI for that at all.
     */
    public function game($gameId)
    {
        $game = $this->repoGame->findWithoutCache($gameId);

        if (!$game) {
            abort(404);
        }

        $pageTitle = $game->title;
        $bindings = $this->pageBuilder->build(
            $pageTitle, StaffBreadcrumbs::gamesImagesSubpage($pageTitle)
        )->bindings;

        $image = GameImage::where('game_id', $game->id)->first();

        $bindings['GameData'] = $game;
        $bindings['GameImage'] = $image;
        $bindings['IsInSpaces'] = $image && $image->location === GameImage::LOCATION_SPACES;
        $bindings['PackshotsConfigured'] = $this->packshotsConfigured();

        $bindings['Packshots'] = [
            $this->packshotDetail($game, $image, ImageResolver::TYPE_SQUARE, 'Square'),
            $this->packshotDetail($game, $image, ImageResolver::TYPE_HEADER, 'Header'),
        ];

        return view('staff.games.images.game', $bindings);
    }

    /**
     * Assemble both sides of the story for one packshot type.
     *
     * `resolvedUrl` is what the site would actually serve, so it doubles as the answer to
     * "is this coming from the CDN or the legacy path?" - the two are visually distinct and
     * only the object storage one carries ?v=.
     */
    private function packshotDetail($game, ?GameImage $image, string $type, string $label): array
    {
        $isSquare = $type === ImageResolver::TYPE_SQUARE;

        $legacyFilename = $isSquare ? $game->image_square : $game->image_header;
        $spacesFilename = $image ? ($isSquare ? $image->square_filename : $image->header_filename) : null;
        $updatedAt = $image ? ($isSquare ? $image->square_updated_at : $image->header_updated_at) : null;

        $legacyPath = $isSquare
            ? GameImagesService::PATH_IMAGE_SQUARE
            : GameImagesService::PATH_IMAGE_HEADER;

        return [
            'label'           => $label,
            'type'            => $type,
            'resolvedUrl'     => $this->resolver->url($game, $type),
            'legacyFilename'  => $legacyFilename,
            'legacyExists'    => $legacyFilename
                ? file_exists(public_path() . $legacyPath . $legacyFilename)
                : null,
            'spacesFilename'  => $spacesFilename,
            'spacesKey'       => $spacesFilename
                ? $this->resolver->storageKey($game, $type, $spacesFilename)
                : null,
            'spacesExists'    => $this->spacesObjectExists($game, $type, $spacesFilename),
            'updatedAt'       => $updatedAt,
        ];
    }

    /**
     * Null (rather than false) when the question doesn't apply or can't be asked - no filename
     * recorded, or the disk isn't configured. The view distinguishes the three, because
     * "no object" and "couldn't check" mean very different things when you are about to delete
     * the local copy.
     */
    private function spacesObjectExists($game, string $type, ?string $filename): ?bool
    {
        if (!$filename || !$this->packshotsConfigured()) {
            return null;
        }

        try {
            return Storage::disk(ImageResolver::DISK)
                ->exists($this->resolver->storageKey($game, $type, $filename));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mirrors ImageMigrationController: prod has the page live but may have PACKSHOTS_* unset.
     */
    private function packshotsConfigured(): bool
    {
        $disk = config('filesystems.disks.packshots');

        return !empty($disk['bucket']) && !empty($disk['key']);
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
