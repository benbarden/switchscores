<?php

namespace App\Domain\WeeklyBatch;

use App\Models\Console;
use App\Models\WeeklyBatchItem;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\Category\Repository as CategoryRepository;

class CategorySuggester
{
    private const SCORE_DEFINITIVE = 4;
    private const SCORE_STRONG     = 2;
    private const SCORE_WEAK       = 1;

    private const THRESHOLD_HIGH   = 4;
    private const THRESHOLD_MEDIUM = 2;
    private const THRESHOLD_LOW    = 1;

    // Collections that bypass scoring entirely — always Arcade, no exceptions
    private const BYPASS_COLLECTIONS = [
        'arcade-archives'   => 'Arcade',
        'arcade-archives-2' => 'Arcade',
        'aca-neogeo'        => 'Arcade',
    ];

    // Nintendo genres that contribute nothing — too vague or useless
    private const IGNORED_GENRES = [
        'Action', 'Communication', 'Other', 'Study', 'Training', 'Toy', 'Utility',
    ];

    // Specific/unambiguous Nintendo genres → SS category (SCORE_DEFINITIVE — High alone)
    private const SPECIFIC_GENRES = [
        "Shoot 'Em Up"  => "Shoot 'em up",
        "Shoot 'em Up"  => "Shoot 'em up",
        'Shoot Em Up'   => "Shoot 'em up",
        'Visual Novel'  => 'Visual novel',
        'Rhythm'        => 'Music',
        'Pinball'       => 'Pinball',
        'Run-and-Gun'   => 'Run-and-gun',
        'Run and Gun'   => 'Run-and-gun',
        'Roguelike'     => 'Roguelike',
        'Rogue-like'    => 'Roguelike',
        'Board Game'    => 'Board and Tabletop',
        'Card Game'     => 'Card game',
        'Trivia'        => 'Quiz',
        'Metroidvania'  => 'Metroidvania',
    ];

    // Generic Nintendo genres → SS category (SCORE_STRONG — needs corroboration)
    private const GENERIC_GENRES = [
        'Adventure'        => 'Adventure',
        'Arcade'           => 'Arcade',
        'Fighting'         => 'Fighting',
        'Health & Fitness' => 'Lifestyle',
        'Music'            => 'Music',
        'Party'            => 'Party',
        'Platformer'       => 'Platformer',
        'Platform'         => 'Platformer',
        'Puzzle'           => 'Puzzle',
        'Racing'           => 'Racing',
        'RPG'              => 'RPG',
        'Role-Playing'     => 'RPG',
        'Shooter'          => 'Shooting',
        'Simulation'       => 'Simulation',
        'Sports'           => 'Sports',
        'Strategy'         => 'Strategy',
        'Educational'      => 'Educational',
        'Survival'         => 'Survival',
    ];

    // Publisher name patterns → SS category (SCORE_STRONG — needs corroboration)
    private const PUBLISHER_RULES = [
        'Kemco'     => 'RPG',
        'Kairosoft' => 'Management',
    ];

    // Phrases in title or description → SS category (SCORE_DEFINITIVE — High alone)
    // More specific phrases listed first to avoid partial matches shadowing them
    private const PHRASE_MAP = [
        'action-RPG'              => 'Action RPG',
        'action RPG'              => 'Action RPG',
        'action role-playing'     => 'Action RPG',
        'action role playing'     => 'Action RPG',
        'turn-based RPG'          => 'Tactical RPG',
        'turn based RPG'          => 'Tactical RPG',
        'turn-based role-playing' => 'Tactical RPG',
        'strategy RPG'            => 'Tactical RPG',
        'tactical RPG'            => 'Tactical RPG',
        'TRPG'                    => 'Tactical RPG',
        'dungeon crawler'         => 'Dungeon crawler',
        'dungeon-crawler'         => 'Dungeon crawler',
        'city-building'           => 'City building',
        'city building'           => 'City building',
        'city builder'            => 'City building',
        'city-builder'            => 'City building',
        'tower defense'           => 'Tower defense',
        'tower defence'           => 'Tower defense',
        'tower-defense'           => 'Tower defense',
        'tower-defence'           => 'Tower defense',
        'visual novel'            => 'Visual novel',
        'farming simulator'       => 'Farming',
        'farming sim'             => 'Farming',
        'farming game'            => 'Farming',
        'management simulator'    => 'Management',
        'management sim'          => 'Management',
        'management game'         => 'Management',
        "beat 'em up"             => "Beat 'em up",
        'beat em up'              => "Beat 'em up",
        "shoot 'em up"            => "Shoot 'em up",
        'shoot em up'             => "Shoot 'em up",
        'bullet hell'             => "Shoot 'em up",
        'metroidvania'            => 'Metroidvania',
        'roguelike'               => 'Roguelike',
        'rogue-like'              => 'Roguelike',
        'roguelite'               => 'Roguelike',
        'rogue-lite'              => 'Roguelike',
        'kart racing'             => 'Kart racing',
        'escape room'             => 'Logic puzzle',
        'spot the difference'     => 'Matching puzzle',
        'find the difference'     => 'Matching puzzle',
        'hidden object'           => 'Hidden objects',
        'hidden-object'           => 'Hidden objects',
        'life simulator'          => 'Life simulator',
        'life sim'                => 'Life simulator',
        'flight simulator'        => 'Flight simulator',
        'flight sim'              => 'Flight simulator',
        'twin-stick shooter'      => 'Twin-stick shooter',
        'twin stick shooter'      => 'Twin-stick shooter',
        'arena shooter'           => 'Arena shooter',
        'point and click'         => 'Point and click',
        'point-and-click'         => 'Point and click',
        'interactive story'       => 'Interactive story',
        'interactive fiction'     => 'Interactive story',
        'run-and-gun'             => 'Run-and-gun',
        'run and gun'             => 'Run-and-gun',
        'card combat'             => 'Card game',
        'card battle'             => 'Card game',
        'card-based combat'       => 'Card game',
        'deck builder'            => 'Card game',
        'deck-builder'            => 'Card game',
        'deckbuilder'             => 'Card game',
        'survivor roguelike'      => 'Survivor roguelike',
        'survival roguelike'      => 'Survivor roguelike',
        'survivors'               => 'Survivor roguelike',
        'brick-breaking'          => 'Brick breaking',
        'brick breaking'          => 'Brick breaking',
        'block breaker'           => 'Brick breaking',
        'breakout'                => 'Brick breaking',
        'survival horror'         => 'Horror',
        'horror game'             => 'Horror',
        'horror masterpiece'      => 'Horror',
        'colouring book'          => 'Educational',
        'coloring book'           => 'Educational',
        'colour by number'        => 'Educational',
        'color by number'         => 'Educational',
        'for toddlers'            => 'Educational',
        'kids and toddlers'       => 'Educational',
        'for young children'      => 'Educational',
    ];

    // parentMap built from DB: child category name => parent category name
    private array $parentMap = [];

    // Series list cached at construction: only entries with category_hints set
    private array $seriesList = [];

    public function __construct(
        private WeeklyBatchItemRepository $repoItem,
        private CategoryRepository $repoCategory,
        private GameRepository $repoGame,
        private GameSeriesRepository $repoGameSeries
    ) {
        $this->buildParentMap();
        $this->seriesList = $this->repoGameSeries->getAll()
            ->filter(fn($s) => !empty($s->category_hints))
            ->all();
    }

    private function buildParentMap(): void
    {
        $all      = $this->repoCategory->getAll();
        $topLevel = $all->whereNull('parent_id');
        foreach ($topLevel as $parent) {
            foreach ($all->where('parent_id', $parent->id) as $child) {
                $this->parentMap[$child->name] = $parent->name;
            }
        }
    }

    /**
     * Returns ['category' => string|null, 'confidence' => 'high'|'medium'|'low'|'none', 'reason' => string]
     */
    public function suggest(WeeklyBatchItem $item): array
    {
        $scores  = [];
        $signals = [];

        $this->scoreGenres($item->nintendo_genres ?? '', $scores, $signals);
        $this->scorePublisher($item->publisher_normalised ?? '', $scores, $signals);
        $this->scorePhrases($item->description ?? '', $scores, $signals, 'Description');
        $this->scorePhrases($item->title ?? '', $scores, $signals, 'Title');
        $this->scoreHistory($item, $scores, $signals);
        $this->scoreCrossConsole($item, $scores, $signals);
        $this->scoreSeries($item, $scores, $signals);

        // Bypass collections default to their mapped category, but yield to any other
        // category that already reached SCORE_DEFINITIVE via phrase/genre signals.
        // We override (not add to) the collection category score so genre tags for the
        // same category (e.g. Nintendo genre "Arcade") don't stack above SCORE_DEFINITIVE
        // and drown out legitimate phrase matches.
        if ($item->collection && isset(self::BYPASS_COLLECTIONS[$item->collection])) {
            $collectionCat    = self::BYPASS_COLLECTIONS[$item->collection];
            $otherDefinitive  = false;
            foreach ($scores as $cat => $score) {
                if ($cat !== $collectionCat && $score >= self::SCORE_DEFINITIVE) {
                    $otherDefinitive = true;
                    break;
                }
            }
            if (!$otherDefinitive) {
                $scores[$collectionCat]  = self::SCORE_DEFINITIVE;
                $signals[$collectionCat] = [['label' => "Collection: {$item->collection}", 'score' => self::SCORE_DEFINITIVE]];
            }
        }

        if (empty($scores)) {
            return ['category' => null, 'confidence' => 'none', 'score' => 0, 'reason' => ''];
        }

        arsort($scores);
        $topScore      = reset($scores);
        $topCandidates = array_keys(array_filter($scores, fn($s) => $s === $topScore));

        // Multiple categories tied below definitive threshold — no clear winner
        if ($topScore < self::SCORE_DEFINITIVE && count($topCandidates) > 1) {
            $tied = implode(', ', array_map(fn($cat) => "{$cat} (+{$topScore})", $topCandidates));
            return ['category' => null, 'confidence' => 'none', 'score' => $topScore, 'reason' => "Tied: {$tied}"];
        }

        $winner     = $this->resolveWinner($scores);
        $confidence = $this->scoreToConfidence($scores[$winner]);
        $reason     = $this->buildReason($winner, $scores, $signals);

        return ['category' => $winner, 'confidence' => $confidence, 'score' => $scores[$winner], 'reason' => $reason];
    }

    private function scoreGenres(string $genresRaw, array &$scores, array &$signals): void
    {
        if (trim($genresRaw) === '') return;

        foreach (array_map('trim', explode(',', $genresRaw)) as $genre) {
            if (in_array($genre, self::IGNORED_GENRES)) continue;

            foreach (self::SPECIFIC_GENRES as $key => $cat) {
                if (strcasecmp($genre, $key) === 0) {
                    $this->addScore($scores, $signals, $cat, self::SCORE_DEFINITIVE, "Genre: {$genre}");
                    continue 2;
                }
            }

            foreach (self::GENERIC_GENRES as $key => $cat) {
                if (strcasecmp($genre, $key) === 0) {
                    $this->addScore($scores, $signals, $cat, self::SCORE_STRONG, "Genre: {$genre}");
                    continue 2;
                }
            }
        }
    }

    private function scorePublisher(string $publisher, array &$scores, array &$signals): void
    {
        if (trim($publisher) === '') return;

        foreach (self::PUBLISHER_RULES as $pattern => $cat) {
            if (stripos($publisher, $pattern) !== false) {
                $this->addScore($scores, $signals, $cat, self::SCORE_STRONG, "Publisher: {$publisher}");
                return;
            }
        }
    }

    private function scorePhrases(string $text, array &$scores, array &$signals, string $source): void
    {
        if (trim($text) === '') return;

        // Normalise curly/typographic apostrophes and quotes to straight equivalents
        $text  = str_replace(["\u{2018}", "\u{2019}", "\u{201B}"], "'", $text);
        $lower = strtolower($text);

        foreach (self::PHRASE_MAP as $phrase => $cat) {
            if (str_contains($lower, strtolower($phrase))) {
                $this->addScore($scores, $signals, $cat, self::SCORE_DEFINITIVE, "{$source}: \"{$phrase}\"");
            }
        }
    }

    private function scoreHistory(WeeklyBatchItem $item, array &$scores, array &$signals): void
    {
        // Collection history for non-bypass collections (stronger signal than publisher history)
        if ($item->collection && !isset(self::BYPASS_COLLECTIONS[$item->collection])) {
            $hist = $this->repoItem->getCategoryHistoryByCollection($item->collection, $item->batch_id);
            if ($hist) {
                $this->addScore($scores, $signals, $hist, self::SCORE_STRONG, "Collection history: {$item->collection}");
            }
        }

        // Publisher history
        if ($item->publisher_normalised) {
            $hist = $this->repoItem->getCategoryHistory($item->publisher_normalised, $item->batch_id);
            if ($hist) {
                $this->addScore($scores, $signals, $hist, self::SCORE_WEAK, "DB history: {$item->publisher_normalised}");
            }
        }
    }

    private function scoreSeries(WeeklyBatchItem $item, array &$scores, array &$signals): void
    {
        $titleLower = strtolower($item->title);

        foreach ($this->seriesList as $series) {
            if (!str_contains($titleLower, strtolower($series->series))) {
                continue;
            }

            $hints = $series->category_hints;

            if (count($hints) === 1) {
                $this->addScore($scores, $signals, $hints[0], self::SCORE_DEFINITIVE, "Series: {$series->series}");
            }
            // Multiple hints = ambiguous, no signal added
            return;
        }
    }

    private function scoreCrossConsole(WeeklyBatchItem $item, array &$scores, array &$signals): void
    {
        $otherConsoleId = $item->console === 'switch-2' ? Console::ID_SWITCH_1 : Console::ID_SWITCH_2;

        $titlesToTry = array_unique([$item->title, $this->stripConsoleSuffix($item->title)]);

        foreach ($titlesToTry as $title) {
            $game = $this->repoGame->findByTitleAndConsole($title, $otherConsoleId);
            if ($game && $game->category) {
                $this->addScore($scores, $signals, $game->category->name, self::SCORE_DEFINITIVE, "Other console: {$game->title}");
                return;
            }
        }
    }

    private function stripConsoleSuffix(string $title): string
    {
        $suffixes = [
            ': Nintendo Switch 2 Edition',
            ' Nintendo Switch 2 Edition',
            ' Switch 2 Edition',
            ' (Nintendo Switch 2 Edition)',
            ' (Switch 2 Edition)',
            ' for Nintendo Switch 2',
            ' (Switch 2)',
        ];

        foreach ($suffixes as $suffix) {
            if (str_ends_with($title, $suffix)) {
                return substr($title, 0, -strlen($suffix));
            }
        }

        return $title;
    }

    private function addScore(array &$scores, array &$signals, string $category, int $score, string $label): void
    {
        $scores[$category]    = ($scores[$category] ?? 0) + $score;
        $signals[$category][] = ['label' => $label, 'score' => $score];
    }

    /**
     * Picks the winner. When scores tie, prefers a subcategory over its parent.
     * Also prefers subcategory when it scores within 1 point of its parent.
     */
    private function resolveWinner(array $scores): string
    {
        $topScore = reset($scores);
        $topCat   = array_key_first($scores);

        foreach ($scores as $cat => $score) {
            if ($cat === $topCat) continue;
            if ($score < $topScore - 1) break;

            // $cat is a child of the current winner → prefer the child
            if (($this->parentMap[$cat] ?? null) === $topCat) {
                return $cat;
            }
        }

        return $topCat;
    }

    private function scoreToConfidence(int $score): string
    {
        if ($score >= self::THRESHOLD_HIGH)   return 'high';
        if ($score >= self::THRESHOLD_MEDIUM) return 'medium';
        if ($score >= self::THRESHOLD_LOW)    return 'low';
        return 'none';
    }

    private function buildReason(string $winner, array $scores, array $signals): string
    {
        $parts        = [];
        $winnerParent = $this->parentMap[$winner] ?? null;

        // Supporting signals: winner's own signals + parent's signals (if winner is a child)
        $supporting = $signals[$winner] ?? [];
        if ($winnerParent && isset($signals[$winnerParent])) {
            foreach ($signals[$winnerParent] as $sig) {
                $supporting[] = $sig;
            }
        }
        foreach ($supporting as $sig) {
            $parts[] = $sig['label'] . ' (+' . $sig['score'] . ')';
        }

        // Conflicting signals: other categories scoring >= SCORE_STRONG, excluding the winner's parent
        $conflicts = [];
        foreach ($scores as $cat => $score) {
            if ($cat === $winner || $cat === $winnerParent) continue;
            if ($score < self::SCORE_STRONG) continue;
            $conflicts[] = $cat . ' (+' . $score . ')';
        }
        if (!empty($conflicts)) {
            $parts[] = 'Conflicts: ' . implode(', ', $conflicts);
        }

        return implode('; ', $parts);
    }
}
