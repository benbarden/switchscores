<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Carbon\Carbon;

use App\Construction\Game\GameBuilder;
use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\Url\LinkTitle as LinkTitleGenerator;
use App\Models\Console;
use App\Models\Game;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Game\Repository as GameRepository;

class ReleaseHubController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameRepository $repoGame,
        private HashGeneratorRepository $gameTitleHashGenerator,
        private GameTitleHashRepository $repoGameTitleHash,
        private LinkTitleGenerator $linkTitleGenerator,
        private DownloadPackshotHelper $downloadPackshotHelper
    )
    {
    }

    private function smartSortDir(string $from, string $to): string
    {
        $today = Carbon::today();
        $start = Carbon::parse($from);
        $end   = Carbon::parse($to);

        if ($start->gt($today)) return 'asc';  // future only
        return 'desc';                          // everything else -> newest first
    }

    public function show(Request $request)
    {
        $pageTitle = 'Release hub';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $consoleId = $request->get('consoleId');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $customStart = $request->get('customStart');
        $customEnd = $request->get('customEnd');

        // 1) Read user choice first
        $sortDir = $request->get('sort'); // 'asc' or 'desc'

        // 2) If not provided, compute smart default
        if (!$sortDir) {
            if ($startDate && $endDate) {
                $sortDir = $this->smartSortDir($startDate, $endDate);
            } elseif ($customStart && $customEnd) {
                $sortDir = $this->smartSortDir($customStart, $customEnd);
            } else {
                $sortDir = 'asc'; // safe default when no range yet
            }
        }

        $bindings['SortDir'] = $sortDir;

        // Build list of week options
        $weeks = [];
        $baseSaturday = Carbon::now()->startOfWeek(Carbon::SATURDAY); // this week's Saturday
        for ($i = -2; $i <= 2; $i++) {
            $start = $baseSaturday->copy()->addWeeks($i);
            $end   = $start->copy()->addDays(6);
            $weeks[] = [
                'label' => $start->format('D j M Y') . ' – ' . $end->format('D j M Y'),
                'start' => $start->toDateString(),
                'end'   => $end->toDateString(),
            ];
        }
        $bindings['WeekList'] = $weeks;
        $bindings['StartDate'] = $startDate;
        $bindings['EndDate'] = $endDate;
        $bindings['CustomStart'] = $customStart;
        $bindings['CustomEnd'] = $customEnd;

        $consoleList = [
            [
                'id' => Console::ID_SWITCH_1,
                'name' => Console::DESC_SWITCH_1,
            ],
            [
                'id' => Console::ID_SWITCH_2,
                'name' => Console::DESC_SWITCH_2,
            ],
        ];

        $bindings['ConsoleList'] = $consoleList;
        $bindings['ConsoleId'] = $consoleId;

        $gameList = null;
        if ($consoleId && $startDate && $endDate) {
            $gameList = $this->repoGameLists->gamesForReleaseHub($consoleId, $startDate, $endDate, $sortDir);
        } elseif ($consoleId && $customStart && $customEnd) {
            $gameList = $this->repoGameLists->gamesForReleaseHub($consoleId, $customStart, $customEnd, $sortDir);
        }

        if ($gameList) {
            $bindings['GameList'] = $gameList;
        }

        return view('staff.games.release-hub.show', $bindings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'console_id' => 'required|integer|exists:consoles,id',
            'title' => 'required|string|max:255',
            'release_date' => 'required|date',
            'price' => 'nullable|string|max:50',
            'nintendo_url' => 'nullable|url',
            'image_url' => 'nullable|url',
        ]);

        // Assign fields
        $title = $validated['title'];
        $releaseDate = $validated['release_date'];
        $price = $validated['price'];
        $nintendoUrl = $validated['nintendo_url'];
        $imageUrl = $validated['image_url'];
        $consoleId = $validated['console_id'];

        // Check title hash is unique
        $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
        $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

        // Check for duplicates
        if ($hashExists) {
            return response()->json([
                'success' => false,
                'message' => 'A game with this title already exists.'
            ], 422);
        }

        // OK to proceed
        $linkTitle = $this->linkTitleGenerator->generate($title);
        $gameBuilder = new GameBuilder();
        $gameBuilder->setTitle($title);
        $gameBuilder->setLinkTitle($linkTitle);
        $gameBuilder->setReviewCount(0);
        $gameBuilder->setAddedBatchDateToToday();
        if ($consoleId) {
            $gameBuilder->setConsoleId($consoleId);
        }
        if ($releaseDate) {
            $gameBuilder->setEuReleaseDate($releaseDate);
        }
        if ($price) {
            $gameBuilder->setPriceEshop($price);
        }
        if ($nintendoUrl) {
            $gameBuilder->setNintendoStoreUrlOverride($nintendoUrl);
        }
        if ($imageUrl) {
            $gameBuilder->setPackshotSquareUrlOverride($imageUrl);
        }
        $game = $gameBuilder->getGame();
        $game->save();
        $gameId = $game->id;

        // Add title hash
        $this->repoGameTitleHash->create($title, $hashedTitle, $gameId);

        // Add publisher, if selected
        // @todo
        /*
        if (array_key_exists('publisher_id_'.$i, $postData)) {
            $publisherId = $postData['publisher_id_'.$i];
            $gamesCompany = $this->repoGamesCompany->find($publisherId);
            if ($gamesCompany) {
                $this->repoGamePublisher->create($gameId, $publisherId);
                $this->gameQualityFilter->updateGame($game, $gamesCompany);
            }
        }
        */

        // Download image
        $this->downloadPackshotHelper->downloadForGame($game);

        // Return HTML for the new table row
        return response()->json([
            'success' => true,
            'html' => view('staff.games.release-hub._game-row', [
                'game' => $game->load([
                    'console',
                    'gamePublishers.publisher',
                    'category',
                ]),
            ])->render()
        ]);
    }

    public function toggleRelease($id)
    {
        $game = $this->repoGame->find($id);
        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found.'
            ], 404);
        }

        // Don't allow future releases to be released early
        if ($game->eu_is_released == 0) {
            if (Carbon::parse($game->eu_release_date)->isFuture()) {
                return response()->json(['error' => 'Cannot release before release date'], 422);
            }
        }

        // Flip the flag
        if ($game->eu_is_released == 1) {
            $this->repoGame->unmarkAsReleased($game);
        } else {
            $this->repoGame->markAsReleased($game);
        }
        $this->repoGame->clearCacheCoreData($id);

        return response()->json([
            'success' => true,
            'id' => $game->id,
            'html' => view('staff.games.release-hub._game-row', [
                'game' => $game->load(['console', 'gamePublishers.publisher', 'category'])
            ])->render()
        ]);
    }

    public function reorder(Request $request)
    {
        $date = $request->input('date');
        $ids = $request->input('order', []);

        foreach ($ids as $position => $id) {
            Game::where('id', $id)
                ->whereDate('eu_release_date', $date)
                ->update(['eshop_europe_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }
}
