<?php

namespace App\Domain\GameImport;

/**
 * Result object containing validated import data and any errors.
 */
class ImportResult
{
    /**
     * @param ImportGameData[] $validGames Games that passed validation
     * @param array $gameValidation Per-game validation info (consoleId, categoryId, publisherId, etc.)
     * @param string[] $newPublishers Publisher names that will be auto-created
     * @param array $errors Validation errors keyed by game index
     * @param string|null $batchDate The batch date from the JSON file
     */
    public function __construct(
        public array $validGames = [],
        public array $gameValidation = [],
        public array $newPublishers = [],
        public array $errors = [],
        public ?string $batchDate = null,
    ) {
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function hasValidGames(): bool
    {
        return count($this->validGames) > 0;
    }

    public function hasNewPublishers(): bool
    {
        return count($this->newPublishers) > 0;
    }

    public function addValidGame(ImportGameData $game, array $validation): void
    {
        $this->validGames[] = $game;
        $this->gameValidation[] = $validation;
    }

    public function addError(int $index, string $title, string $error): void
    {
        $this->errors[] = [
            'index' => $index,
            'title' => $title,
            'error' => $error,
        ];
    }

    public function addNewPublisher(string $publisherName): void
    {
        if (!in_array($publisherName, $this->newPublishers)) {
            $this->newPublishers[] = $publisherName;
        }
    }

    public function toArray(): array
    {
        $games = [];
        foreach ($this->validGames as $index => $game) {
            $games[] = [
                'title' => $game->title,
                'releaseDate' => $game->releaseDate,
                'priceGbp' => $game->priceGbp,
                'url' => $game->url,
                'packshotUrl' => $game->packshotUrl,
                'publisher' => $game->publisher,
                'players' => $game->players,
                'category' => $game->category,
                'consoleSlug' => $game->consoleSlug,
                'sourceFile' => $game->sourceFile,
                'validation' => $this->gameValidation[$index] ?? [],
            ];
        }

        return [
            'validGames' => $games,
            'newPublishers' => $this->newPublishers,
            'errors' => $this->errors,
            'batchDate' => $this->batchDate,
        ];
    }

    public static function fromArray(array $data): self
    {
        $result = new self();
        $result->batchDate = $data['batchDate'] ?? null;
        $result->newPublishers = $data['newPublishers'] ?? [];
        $result->errors = $data['errors'] ?? [];

        foreach ($data['validGames'] ?? [] as $gameData) {
            $game = ImportGameData::fromArray([
                'title' => $gameData['title'],
                'release_date' => $gameData['releaseDate'],
                'price_gbp' => $gameData['priceGbp'],
                'url' => $gameData['url'],
                'packshot_url' => $gameData['packshotUrl'],
                'publisher' => $gameData['publisher'],
                'players' => $gameData['players'],
                'category' => $gameData['category'],
                'console_slug' => $gameData['consoleSlug'],
                'source_file' => $gameData['sourceFile'],
            ]);
            $result->addValidGame($game, $gameData['validation'] ?? []);
        }

        return $result;
    }
}
