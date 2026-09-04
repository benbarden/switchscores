<?php

namespace App\Domain\GameImport;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\Url\LinkTitle;
use App\Events\GameCreated;
use App\Models\Console;
use App\Models\Game;

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
     * Import a single game into the games table.
     *
     * Takes a value object rather than a weekly batch item, so any source can use it -
     * the weekly batch calls WeeklyBatchItem::toImportGameData() (improvement #135).
     * One game per call: callers that import a listing work out eshop_europe_order
     * across the whole listing first, which is not this class's business.
     *
     * Returns an array:
     *   'game'           => Game
     *   'new_publisher'  => string|null  — publisher name if auto-created
     *   'packshot_ok'    => bool
     *   'header_ok'      => bool
     */
    public function importItem(ImportGameData $data, ?int $eshopOrder = null): array
    {
        $consoleId = $data->consoleSlug === 'switch-2' ? Console::ID_SWITCH_2 : Console::ID_SWITCH_1;

        $category  = $this->repoCategory->getByName($data->category);
        $linkTitle = (new LinkTitle())->generate($data->title);
        $titleHash = $this->hashGenerator->generateHash($data->title);

        $params = [
            'title'                       => $data->title,
            'link_title'                  => $linkTitle,
            'console_id'                  => $consoleId,
            'category_id'                 => $category?->id,
            'eu_release_date'             => $data->releaseDate,
            'price_eshop'                 => $data->priceGbp === null ? null : number_format($data->priceGbp, 2, '.', ''),
            'players'                     => $data->players,
            'nintendo_store_url_override' => $data->url,
        ];

        if ($eshopOrder !== null) {
            $params['eshop_europe_order'] = $eshopOrder;
        }

        if ($data->collection) {
            $collection = $this->repoGameCollection->getByLinkTitle($data->collection);
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

        if ($data->description) {
            $game->nintendo_description = $this->cleanNintendoDescription($data->description);
        }

        // Crawl priority is set by PrioritiseNewGameForCrawl on the GameCreated event.
        $game->save();
        $gameId = $game->id;

        // Title hash
        $this->repoTitleHash->create($data->title, $titleHash, $gameId, $consoleId);

        // Publisher — link to existing record (created in the Publishers step)
        if ($data->publisher) {
            $company = $this->repoGamesCompany->findByNameCaseInsensitive($data->publisher);
            if ($company) {
                $this->repoGamePublisher->create($gameId, $company->id);
                $this->gameQualityFilter->updateGame($game, $company);
            }
        }

        // Packshot (square image)
        $packshotOk = false;
        if ($data->packshotUrl) {
            $packshotOk = $this->squareImageDownloader->download($game, $data->packshotUrl);
        }

        // Header image from Nintendo store page
        $headerOk = false;
        if ($data->url) {
            $headerOk = $this->headerImageScraper->downloadFromStorePage($game, $data->url);
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
