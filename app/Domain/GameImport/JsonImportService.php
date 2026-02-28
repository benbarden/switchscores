<?php

namespace App\Domain\GameImport;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\Url\LinkTitle;
use App\Events\GameCreated;
use App\Factories\GameDirectorFactory;
use App\Models\Console;
use App\Models\Game;
use App\Models\GamesCompany;

class JsonImportService
{
    private const CONSOLE_SLUG_MAP = [
        'switch-1' => Console::ID_SWITCH_1,
        'switch-2' => Console::ID_SWITCH_2,
    ];

    public function __construct(
        private CategoryRepository $categoryRepository,
        private GamesCompanyRepository $gamesCompanyRepository,
        private GameTitleHashRepository $gameTitleHashRepository,
        private GamePublisherRepository $gamePublisherRepository,
        private HashGenerator $hashGenerator,
        private LinkTitle $linkTitleGenerator,
        private GameQualityFilter $gameQualityFilter,
        private SquareImageDownloader $squareImageDownloader,
        private HeaderImageScraper $headerImageScraper,
    ) {
    }

    /**
     * Parse JSON content and validate all games.
     */
    public function parseAndValidate(string $jsonContent): ImportResult
    {
        $result = new ImportResult();

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result->addError(0, '', 'Invalid JSON: ' . json_last_error_msg());
            return $result;
        }

        $result->batchDate = $data['batch_date'] ?? null;
        $games = $data['games'] ?? [];

        if (empty($games)) {
            $result->addError(0, '', 'No games found in JSON file');
            return $result;
        }

        foreach ($games as $index => $gameData) {
            $this->validateGame($index, $gameData, $result);
        }

        return $result;
    }

    private function validateGame(int $index, array $gameData, ImportResult $result): void
    {
        $game = ImportGameData::fromArray($gameData);
        $validation = [];

        // Title is required
        if (empty($game->title)) {
            $result->addError($index, '(no title)', 'Title is required');
            return;
        }

        // Check for duplicate title
        $titleHash = $this->hashGenerator->generateHash($game->title);
        if ($this->gameTitleHashRepository->titleHashExists($titleHash)) {
            $result->addError($index, $game->title, 'Game already exists (duplicate title)');
            return;
        }

        // Validate console
        $consoleId = self::CONSOLE_SLUG_MAP[$game->consoleSlug] ?? null;
        if (!$consoleId) {
            $result->addError($index, $game->title, "Invalid console slug: {$game->consoleSlug}");
            return;
        }
        $validation['consoleId'] = $consoleId;

        // Validate category (optional but must exist if provided)
        $categoryId = null;
        if ($game->category) {
            $category = $this->categoryRepository->getByName($game->category);
            if (!$category) {
                $result->addError($index, $game->title, "Category not found: {$game->category}");
                return;
            }
            $categoryId = $category->id;
        }
        $validation['categoryId'] = $categoryId;

        // Check publisher - may need to be auto-created
        $publisherId = null;
        $isNewPublisher = false;
        if ($game->publisher) {
            $publisher = $this->gamesCompanyRepository->getByName($game->publisher);
            if ($publisher) {
                $publisherId = $publisher->id;
            } else {
                $isNewPublisher = true;
                $result->addNewPublisher($game->publisher);
            }
        }
        $validation['publisherId'] = $publisherId;
        $validation['isNewPublisher'] = $isNewPublisher;
        $validation['publisherName'] = $game->publisher;

        // Generate link title
        $linkTitle = $this->linkTitleGenerator->generate($game->title);
        $validation['linkTitle'] = $linkTitle;

        // All validations passed
        $result->addValidGame($game, $validation);
    }

    /**
     * Execute the import, creating games and publishers.
     *
     * @return Game[] Array of created games
     */
    public function executeImport(ImportResult $result): array
    {
        $createdGames = [];
        $createdPublishers = [];

        // First, create any new publishers
        foreach ($result->newPublishers as $publisherName) {
            $publisher = $this->createPublisher($publisherName);
            $createdPublishers[$publisherName] = $publisher;
        }

        // Now create all games
        foreach ($result->validGames as $index => $gameData) {
            $validation = $result->gameValidation[$index];

            // Resolve publisher ID (may have just been created)
            $publisherId = $validation['publisherId'];
            if ($validation['isNewPublisher'] && isset($createdPublishers[$validation['publisherName']])) {
                $publisherId = $createdPublishers[$validation['publisherName']]->id;
            }

            $game = $this->createGame($gameData, $validation, $result->batchDate);

            // Link publisher if we have one
            if ($publisherId) {
                $this->gamePublisherRepository->create($game->id, $publisherId);
                $publisher = $this->gamesCompanyRepository->find($publisherId);
                if ($publisher) {
                    $this->gameQualityFilter->updateGame($game, $publisher);
                }
            }

            // Create title hash for duplicate detection
            $titleLowercase = strtolower($gameData->title);
            $titleHash = $this->hashGenerator->generateHash($gameData->title);
            $this->gameTitleHashRepository->create($titleLowercase, $titleHash, $game->id);

            // Download images
            if ($gameData->packshotUrl) {
                $this->squareImageDownloader->download($game, $gameData->packshotUrl);
            }
            if ($gameData->url) {
                $this->headerImageScraper->downloadFromStorePage($game, $gameData->url);
            }

            // Fire event
            event(new GameCreated($game));

            $createdGames[] = $game;
        }

        return $createdGames;
    }

    private function createPublisher(string $name): GamesCompany
    {
        $linkTitle = $this->linkTitleGenerator->generate($name);

        $publisher = new GamesCompany();
        $publisher->name = $name;
        $publisher->link_title = $linkTitle;
        $publisher->is_low_quality = 0;
        $publisher->save();

        return $publisher;
    }

    private function createGame(ImportGameData $gameData, array $validation, ?string $batchDate): Game
    {
        $params = $gameData->toGameParams(
            consoleId: $validation['consoleId'],
            categoryId: $validation['categoryId'],
            linkTitle: $validation['linkTitle'],
            batchDate: $batchDate
        );

        $game = GameDirectorFactory::createNew($params);

        // Set batch date if provided
        if ($batchDate) {
            $game->added_batch_date = $batchDate;
        }

        // Mark category as verified if a category was set via JSON import
        if ($validation['categoryId']) {
            $game->category_verification = 1;
        }

        // Prioritise for crawling
        $game->crawl_priority = true;

        // Save post-creation changes
        $game->save();

        return $game;
    }
}
