<?php

namespace App\Http\Controllers\Staff\Games;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameImport\GameImporter;
use App\Domain\GameImport\ImportGameData;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\WeeklyBatch\CategorySuggester;
use App\Domain\WeeklyBatch\HtmlListParser;
use App\Domain\WeeklyBatch\NintendoPageFetcher;
use App\Domain\WeeklyBatch\ParseService;
use App\Domain\WeeklyBatch\TitleNormaliser;
use App\Models\Console;

/**
 * Add one game missed by the weekly run (#135).
 *
 * Works the way the weekly pipeline does, because it uses the same pieces: the store
 * search-result row is pasted as rich HTML and read by HtmlListParser, the store page
 * behind it is fetched for publisher, players and LQ status, the title is normalised,
 * classified for auto-LQ/bundle/collection, and the category is suggested - then
 * GameImporter writes it. A game added here is the same as one from a batch.
 *
 * Pasting the row matters: the square packshot is only in the listing markup, and the
 * price is only there too (the game page loads it client-side, so it cannot be scraped
 * from the URL). A bare URL still works as a fallback, minus those two fields.
 *
 * One screen rather than the batch's six stages, because the staging exists to manage
 * ~40 games at a time.
 */
class SingleGameAddController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private HtmlListParser $htmlParser,
        private NintendoPageFetcher $fetcher,
        private CategorySuggester $categorySuggester,
        private ParseService $parseService,
        private TitleNormaliser $titleNormaliser,
        private GameImporter $gameImporter,
        private CategoryRepository $repoCategory,
        private GameRepository $repoGame,
        private GameTitleHashRepository $repoTitleHash,
        private HashGenerator $hashGenerator
    ) {
    }

    public function show()
    {
        $pageTitle = 'Add a game from the store';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['ConsoleList']  = [
            ['id' => Console::ID_SWITCH_1, 'name' => 'Switch 1'],
            ['id' => Console::ID_SWITCH_2, 'name' => 'Switch 2'],
        ];
        $bindings['CategoryGroups'] = $this->categoryGroups();

        return view('staff.games.single-game-add.show', $bindings);
    }

    /**
     * Read the pasted row (or the URL), fetch the store page, and return everything
     * the form can prefill.
     */
    public function analyse(Request $request)
    {
        $request->validate([
            'raw_html'     => 'nullable|string',
            'nintendo_url' => 'nullable|string',
            'entry_index'  => 'nullable|integer|min:0',
            'console_id'   => 'nullable|integer|in:'.Console::ID_SWITCH_1.','.Console::ID_SWITCH_2,
        ]);

        $rawHtml = trim((string) $request->input('raw_html', ''));
        $url     = trim((string) $request->input('nintendo_url', ''));

        if ($rawHtml !== '' && $this->htmlParser->looksLikeHtml($rawHtml)) {
            return $this->fromPastedRow($rawHtml, $request);
        }

        if ($url === '') {
            return $this->failed(
                'Paste a game from the Nintendo store search results, or enter a store URL.'
            );
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->failed('That does not look like a URL.');
        }

        return $this->fromUrl($url, $request);
    }

    /**
     * The good path: a store search-result row, which carries the packshot and price.
     */
    private function fromPastedRow(string $rawHtml, Request $request)
    {
        $entries = $this->htmlParser->parse($rawHtml);

        if (empty($entries)) {
            return $this->failed('No games found in that paste. Copy a game row from the store search results.');
        }

        // Several rows pasted - let the user say which one, rather than silently
        // taking the first.
        $index = $request->input('entry_index');
        if (count($entries) > 1 && $index === null) {
            return response()->json([
                'status'     => 'choose',
                'candidates' => array_map(fn($entry, $i) => [
                    'index'        => $i,
                    'title'        => $entry['title_raw'],
                    'console'      => $entry['console_raw'],
                    'release_date' => $entry['release_date'],
                    'price'        => $entry['price_raw'],
                ], $entries, array_keys($entries)),
            ]);
        }

        $entry = $entries[(int) ($index ?? 0)] ?? null;
        if (!$entry) {
            return $this->failed('That game is no longer in the paste. Paste it again.');
        }

        if (empty($entry['nintendo_url'])) {
            return $this->failed('That row has no store link, so the page cannot be fetched.');
        }

        return $this->buildResponse($entry, $request, 'paste');
    }

    /**
     * Fallback: a bare URL. No packshot and no price, because neither is on the page.
     */
    private function fromUrl(string $url, Request $request)
    {
        return $this->buildResponse([
            'title_raw'         => null,
            'nintendo_url'      => $url,
            'packshot_url'      => null,
            'release_date'      => null,
            'nintendo_genres'   => null,
            'nsuid'             => null,
            'console_raw'       => null,
            'price_gbp'         => null,
            'price_raw'         => null,
            'price_flag'        => false,
            'price_flag_reason' => null,
            'description'       => null,
        ], $request, 'url');
    }

    /**
     * Merge what the listing row gave us with what the game page gives us, then run
     * the same classification and category suggestion a batch game gets.
     *
     * The row wins on the fields it carries: those are what the weekly parser stores,
     * and the page either does not have them or has them in a worse form.
     */
    private function buildResponse(array $entry, Request $request, string $source)
    {
        $url = $entry['nintendo_url'];

        $page      = null;
        $pageError = null;
        try {
            $page = $this->fetcher->fetch($url);
        } catch (\Exception $e) {
            $pageError = $e->getMessage();
        }

        // A de-listed game is caught by the fetcher's soft-404 check and lands in the
        // catch above. This is the belt and braces for a page that answers but holds
        // nothing we can read.
        if ($page && !$page['title'] && !$page['publisher_raw']) {
            $page      = null;
            $pageError = 'The store page returned nothing. It may have been taken down.';
        }

        $titleRaw = $entry['title_raw'] ?: ($page['title'] ?? null);

        if (!$titleRaw) {
            return $this->failed($pageError ?: 'Could not work out the game title.');
        }

        $title     = $this->titleNormaliser->normalise($titleRaw);
        $consoleId = $this->resolveConsoleId($request->input('console_id'), $entry['console_raw'] ?? null);
        $price     = $entry['price_gbp'];

        // Same classification a batch game gets. Price is included, so the low-price
        // signal works here exactly as it does in a batch.
        $classification = $this->parseService->classifyActiveItem($title, $price, $url);

        $genres      = $entry['nintendo_genres'] ?: ($page['genres'] ?? null);
        $description = $page['description'] ?? ($entry['description'] ?: null);

        $suggestion = $this->categorySuggester->suggestFor(
            title: $title,
            consoleSlug: $this->consoleSlug($consoleId),
            genres: $genres,
            publisherNormalised: $page['publisher_normalised'] ?? null,
            description: $description,
            collection: $classification['collection'],
            excludeBatchId: null,
        );

        $lqReasons = array_values(array_filter([
            $classification['lq_flag_reason'],
            ($page['lq_flag_reason'] ?? '') ?: null,
            $entry['price_flag'] ? $entry['price_flag_reason'] : null,
        ]));

        return response()->json([
            'status'       => 'ok',
            'source'       => $source,
            'nintendo_url' => $url,
            'title'        => $title,
            'title_raw'    => $titleRaw,
            'console_id'   => $consoleId,
            'console_raw'  => $entry['console_raw'],
            'release_date' => $entry['release_date'] ?: ($page['release_date'] ?? null),
            'price_gbp'    => $price,
            'price_raw'    => $entry['price_raw'],
            'packshot_url' => $entry['packshot_url'] ?: null,
            'nsuid'        => $entry['nsuid'] ?: ($page['nsuid'] ?? null),
            'genres'       => $genres,
            'description'  => $description,
            'publisher'    => $page['publisher_normalised'] ?? null,
            'players'      => $page['players'] ?? null,
            'collection'   => $classification['collection'],
            'lq_confirmed' => $page['lq_confirmed'] ?? false,
            'lq_reasons'   => $lqReasons,
            'auto_status'  => $classification['item_status'],
            'page_error'   => $pageError,
            'category'            => $suggestion['category'],
            'category_confidence' => $suggestion['confidence'],
            'category_score'      => $suggestion['score'] ?? 0,
            'category_reason'     => $suggestion['reason'],
            'duplicate'    => $this->duplicateOf($title, $consoleId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'console_id'   => 'required|integer|in:'.Console::ID_SWITCH_1.','.Console::ID_SWITCH_2,
            'title'        => 'required|string|max:255',
            'release_date' => 'required|date',
            'price_gbp'    => 'nullable|numeric|min:0',
            'category'     => 'nullable|string|max:255',
            'collection'   => 'nullable|string|max:255',
            'nintendo_url' => 'required|url',
            'packshot_url' => 'nullable|url',
            'publisher'    => 'nullable|string|max:255',
            'players'      => 'nullable|string|max:50',
            'description'  => 'nullable|string',
        ]);

        $consoleId = (int) $validated['console_id'];
        $title     = $validated['title'];

        $duplicate = $this->duplicateOf($title, $consoleId);
        if ($duplicate) {
            return redirect()->route('staff.games.single-game-add.show')
                ->withInput()
                ->with('error', "\"{$title}\" already exists on this console (game {$duplicate['id']}).");
        }

        $data = new ImportGameData(
            title: $title,
            releaseDate: $validated['release_date'],
            priceGbp: isset($validated['price_gbp']) ? (float) $validated['price_gbp'] : null,
            url: $validated['nintendo_url'],
            packshotUrl: $validated['packshot_url'] ?? null,
            publisher: $validated['publisher'] ?? null,
            players: $validated['players'] ?? null,
            category: $validated['category'] ?? null,
            collection: $validated['collection'] ?? null,
            series: null,
            consoleSlug: $this->consoleSlug($consoleId),
            sourceFile: null,
            description: $validated['description'] ?? null,
        );

        $eshopOrder = $this->repoGame->getNextEshopOrderForDate($consoleId, $validated['release_date']);

        try {
            $result = $this->gameImporter->importItem($data, $eshopOrder);
        } catch (\Exception $e) {
            return redirect()->route('staff.games.single-game-add.show')
                ->withInput()
                ->with('error', 'Import failed: '.$e->getMessage());
        }

        $game = $result['game'];

        // These are games the weekly run missed, so the release date is usually already
        // past. Nothing marks a game released automatically - it is the Release Hub
        // toggle by hand - so without this the game would import as upcoming and stay
        // that way until someone noticed.
        $released = false;
        if (Carbon::parse($validated['release_date'])->startOfDay()->isPast()) {
            $this->repoGame->markAsReleased($game);
            $released = true;
        }

        $warnings = [];
        if ($data->packshotUrl && !$result['packshot_ok']) {
            $warnings[] = 'the packshot could not be downloaded';
        }
        if (!$result['header_ok']) {
            $warnings[] = 'the header image could not be scraped';
        }

        $redirect = redirect('/staff/games/detail/'.$game->id.'?lastaction=add&lastgameid='.$game->id)
            ->with('success', "\"{$game->title}\" imported"
                .($released ? ' and marked as released.' : ', and left as upcoming.'));

        if ($warnings) {
            $redirect->with('warning', 'Imported, but '.implode(' and ', $warnings).'.');
        }

        return $redirect;
    }

    /**
     * Parent/child grouping for the category dropdown, matching the weekly update
     * category screen so the same list is presented the same way in both places.
     */
    private function categoryGroups(): array
    {
        $allCategories = $this->repoCategory->getAll();
        $topLevel      = $allCategories->whereNull('parent_id')->sortBy('name');

        $groups = [];
        foreach ($topLevel as $parent) {
            $groups[] = [
                'parent'   => $parent->name,
                'children' => $allCategories->where('parent_id', $parent->id)->sortBy('name')->values(),
            ];
        }

        return $groups;
    }

    private function failed(string $message)
    {
        return response()->json(['status' => 'failed', 'message' => $message], 422);
    }

    /**
     * An explicit choice always wins. Otherwise read the console from the row, which
     * reads "Nintendo Switch 2" or "Nintendo Switch" (and both, for dual-console rows -
     * those fall to Switch 1 and the user can switch it).
     */
    private function resolveConsoleId($requested, ?string $consoleRaw): int
    {
        if ($requested) {
            return (int) $requested;
        }

        if ($consoleRaw && !str_contains($consoleRaw, 'Nintendo Switch,')
            && str_contains($consoleRaw, 'Switch 2')) {
            return Console::ID_SWITCH_2;
        }

        return Console::ID_SWITCH_1;
    }

    private function consoleSlug(int $consoleId): string
    {
        return $consoleId === Console::ID_SWITCH_2 ? 'switch-2' : 'switch-1';
    }

    /**
     * Title hash match on the same console, i.e. the check the weekly parser makes
     * before treating a game as new.
     */
    private function duplicateOf(string $title, int $consoleId): ?array
    {
        $hash = $this->hashGenerator->generateHash($title);

        if (!$this->repoTitleHash->titleHashExistsForConsole($hash, $consoleId)) {
            return null;
        }

        $game = $this->repoGame->getByTitleAndConsole($title, $consoleId);

        return [
            'id'    => $game?->id,
            'title' => $game?->title ?? $title,
        ];
    }
}
