<?php

namespace App\Domain\WeeklyBatch;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameImport\HeaderImageScraper;
use App\Domain\GameImport\SquareImageDownloader;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\Url\LinkTitle;
use App\Events\GameCreated;
use App\Models\Console;
use App\Models\Game;
use App\Models\WeeklyBatchItem;

class GameImporter
{
    public function __construct(
        private CategoryRepository $repoCategory,
        private GameRepository $repoGame,
        private GameCollectionRepository $repoGameCollection,
        private GamePublisherRepository $repoGamePublisher,
        private GameTitleHashRepository $repoTitleHash,
        private GamesCompanyRepository $repoGamesCompany,
        private GameQualityFilter $gameQualityFilter,
        private HashGenerator $hashGenerator,
        private SquareImageDownloader $squareImageDownloader,
        private HeaderImageScraper $headerImageScraper,
    ) {}

    /**
     * Import a single ready WeeklyBatchItem into the games table.
     *
     * Returns an array:
     *   'game'           => Game
     *   'new_publisher'  => string|null  — publisher name if auto-created
     *   'packshot_ok'    => bool
     *   'header_ok'      => bool
     */
    public function importItem(WeeklyBatchItem $item, ?int $eshopOrder = null): array
    {
        $consoleId = $item->console === 'switch-2' ? Console::ID_SWITCH_2 : Console::ID_SWITCH_1;

        $category  = $this->repoCategory->getByName($item->category);
        $linkTitle = (new LinkTitle())->generate($item->title);
        $titleHash = $this->hashGenerator->generateHash($item->title);

        $params = [
            'title'                       => $item->title,
            'link_title'                  => $linkTitle,
            'console_id'                  => $consoleId,
            'category_id'                 => $category?->id,
            'eu_release_date'             => $item->release_date?->format('Y-m-d'),
            'price_eshop'                 => $item->price_gbp,
            'players'                     => $item->players,
            'nintendo_store_url_override' => $item->nintendo_url,
        ];

        if ($eshopOrder !== null) {
            $params['eshop_europe_order'] = $eshopOrder;
        }

        if ($item->collection) {
            $collection = $this->repoGameCollection->getByLinkTitle($item->collection);
            if ($collection) {
                $params['collection_id'] = $collection->id;
            }
        }

        $director = new GameDirector();
        $builder  = new GameBuilder();
        $director->setBuilder($builder);
        $director->buildNewGame($params);
        $game = $builder->getGame();

        if ($category) {
            $game->category_verification = 1;
        }

        if ($item->description) {
            $game->nintendo_description = $this->cleanNintendoDescription($item->description);
        }

        $game->crawl_priority = true;
        $game->save();
        $gameId = $game->id;

        // Title hash
        $this->repoTitleHash->create($item->title, $titleHash, $gameId, $consoleId);

        // Publisher — link to existing record (created in the Publishers step)
        if ($item->publisher_normalised) {
            $company = $this->repoGamesCompany->findByNameCaseInsensitive($item->publisher_normalised);
            if ($company) {
                $this->repoGamePublisher->create($gameId, $company->id);
                $this->gameQualityFilter->updateGame($game, $company);
            }
        }

        // Packshot (square image)
        $packshotOk = false;
        if ($item->packshot_url) {
            $packshotOk = $this->squareImageDownloader->download($game, $item->packshot_url);
        }

        // Header image from Nintendo store page
        $headerOk = false;
        if ($item->nintendo_url) {
            $headerOk = $this->headerImageScraper->downloadFromStorePage($game, $item->nintendo_url);
        }

        event(new GameCreated($game));

        return [
            'game'        => $game,
            'packshot_ok' => $packshotOk,
            'header_ok'   => $headerOk,
        ];
    }

    private function cleanNintendoDescription(string $text): string
    {
        // Strip trademark/registered symbols
        $text = str_replace(['™', '®'], '', $text);

        // Replace ■ (section separator) with a newline so sections aren't run together
        $text = str_replace('■', "\n", $text);

        // Collapse multiple consecutive newlines to a maximum of two
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

}
