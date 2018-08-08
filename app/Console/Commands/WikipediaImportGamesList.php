<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\CrawlerWikipediaGamesListSourceService;
use App\Services\GameService;
use App\Services\GameReleaseDateService;
use App\Services\GameTitleHashService;
use App\Services\FeedItemGameService;
use App\Services\HtmlLoader\Wikipedia\Importer;

class WikipediaImportGamesList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WikipediaImportGamesList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports feed items from Wikipedia crawl data, ready to update the games database.';

    /**
     * WikipediaImportGamesList constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Loading source data...');

        $crawlerService = resolve('Services\CrawlerWikipediaGamesListSourceService');
        /* @var CrawlerWikipediaGamesListSourceService $crawlerService */
        $gameTitleHashService = resolve('Services\GameTitleHashService');
        /* @var GameTitleHashService $gameTitleHashService */
        $feedItemGameService = resolve('Services\FeedItemGameService');
        /* @var FeedItemGameService $feedItemGameService */
        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $gameReleaseDateService = resolve('Services\GameReleaseDateService');
        /* @var GameReleaseDateService $gameReleaseDateService */

        $gamesList = $crawlerService->getAll();

        $this->info('Found '.count($gamesList).' item(s)');

        try {

            $importer = new Importer();

            $i = 0;

            foreach ($gamesList as $crawlerModel) {

                $i++;

                $this->line('Processing item: '.$i.' - '.$crawlerModel->title);

                $importer->setCrawlerModel($crawlerModel);
                $feedItemGame = $importer->generateFeedModel();
                $title = $feedItemGame->item_title;

                // Discard games with only TBA/Unreleased dates
                $datesToSkip = ['TBA', 'Unreleased'];
                if ((in_array($feedItemGame->upcoming_date_eu, $datesToSkip)) &&
                    (in_array($feedItemGame->upcoming_date_us, $datesToSkip)) &&
                    (in_array($feedItemGame->upcoming_date_jp, $datesToSkip))) {
                    $this->error('All dates are TBA or Unreleased; skipping');
                    continue;
                }

                // Discard games containing certain text
                $foundSkipText = null;
                $textToSkip = ['Untitled ', '(tentative title)', 'Nintendo Labo'];
                foreach ($textToSkip as $skipText) {
                    if (strpos($title, $skipText) !== false) {
                        $foundSkipText = $skipText;
                        break;
                    }
                }
                if ($foundSkipText != null) {
                    $this->error('Found ignore text: '.$foundSkipText.'; skipping');
                    continue;
                }

                // See if we can locate the game
                $titleHash = $gameTitleHashService->generateHash($title);
                $gameTitleHash = $gameTitleHashService->getByHash($titleHash);
                if ($gameTitleHash) {
                    $gameId = $gameTitleHash->game_id;
                    $feedItemGame->game_id = $gameId;
                } else {
                    $gameId = null;
                }

                // Check if anything's changed since the last record, if one exists
                if ($gameId) {

                    // We need to skip anything that's already waiting to be reviewed.
                    // Otherwise, the list will keep adding duplicates each time the importer runs.
                    $activeFeedItem = $feedItemGameService->getActiveByGameId($gameId);
                    if ($activeFeedItem) {
                        $this->warn('Found an active, unprocessed entry; skipping');
                        continue;
                    }

                    // There isn't a previous entry in feed items, but has the game actually changed?
                    $game = $gameService->find($gameId);
                    $gameReleaseDates = $gameReleaseDateService->getByGame($gameId);
                    $modifiedFieldList = $importer->getGameModifiedFields($feedItemGame, $game, $gameReleaseDates);
                    if (count($modifiedFieldList) == 0) {
                        $this->line('No changes; skipping');
                        continue;
                    } else {
                        $this->info('Changes found');
                        $feedItemGame->modified_fields = serialize($modifiedFieldList);
                    }
                } else {
                    // If we don't know the game id and we haven't dealt with the imported data yet,
                    // duplicates will be added each time the importer is run.
                    // This check will stop those duplicates if an update is pending.
                    $activeFeedItem = $feedItemGameService->getActiveByTitle($title);
                    if ($activeFeedItem) {
                        $this->warn('Found an active, unprocessed entry; skipping');
                        continue;
                    }
                }

                $feedItemGame->save();

                //if ($i > 3) break;

            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
