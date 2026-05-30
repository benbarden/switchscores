<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\DataSourceImportLog;
use App\Models\Game;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\DataSourceImportLog\Repository as DataSourceImportLogRepository;
use App\Domain\DataSourceImportRun\Repository as DataSourceImportRunRepository;

use App\Services\DataSources\NintendoCoUk\Importer;
use App\Services\DataSources\NintendoCoUk\Parser;

class ImportParseLink extends Command
{
    protected $signature = 'DSNintendoCoUkImportParseLink {mode?}';

    protected $description = 'Imports and parses data, then links it to games.';

    public function __construct(
        private DataSourceRepository $repoDataSource,
        private DataSourceRawRepository $repoDataSourceRaw,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private DataSourceImportLogRepository $repoImportLog,
        private DataSourceImportRunRepository $repoImportRun,
        private Importer $importer,
        private Parser $parser
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $argMode = $this->argument('mode');

        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->signature.' *************** ');

        $sourceId = $this->repoDataSource->getSourceNintendoCoUk()->id;

        $run = $this->repoImportRun->startRun($sourceId);
        $runId = $run->id;

        try {

            $allNewIds      = [];
            $allChangedIds  = [];
            $allRelistedIds = [];

            // PARSE-ONLY MODE
            if ($argMode == 'parse') {

                $logger->info('Parse-only mode; skipping data download from eShop.');
                // Parse all non-delisted records
                $allNewIds = $this->repoDataSourceRaw->getBySourceId($sourceId)->pluck('id')->toArray();

            } else {

                $importStart = now();

                $logger->warning('Loading LIVE data from eShop. Do not abuse!');

                $loadLimit = 1000;

                // Switch 1 — get count first, then load in batches
                $this->importer->loadGames(0, 0);
                $switch1Total = $this->importer->getNumFound();
                $logger->info('Switch 1 total items: '.$switch1Total);

                foreach (range(0, $switch1Total - 1, $loadLimit) as $offset) {

                    $logger->info(sprintf('Switch 1: loading %s items at offset %s', $loadLimit, $offset));
                    $this->importer->loadGames($loadLimit, $offset);

                    $gameData = $this->importer->getResponseData()['response']['docs'] ?? null;
                    if (!is_array($gameData)) {
                        $logger->error('Cannot load game data at offset '.$offset);
                        continue;
                    }

                    $result = $this->importer->importToDb($sourceId);
                    $allNewIds      = array_merge($allNewIds, $result['new_ids']);
                    $allChangedIds  = array_merge($allChangedIds, $result['changed_ids']);
                    $allRelistedIds = array_merge($allRelistedIds, $result['relisted_ids']);

                    $logger->info(sprintf('Processed %s item(s)', $this->importer->getImportedCount()));
                }

                // Switch 2
                $logger->info('Loading Switch 2 data...');
                $this->importer->loadGamesSwitch2($loadLimit, 0);

                $gameData = $this->importer->getResponseData()['response']['docs'] ?? null;
                if (!is_array($gameData)) {
                    $logger->error('Cannot load Switch 2 game data');
                } else {
                    $result = $this->importer->importToDb($sourceId);
                    $allNewIds      = array_merge($allNewIds, $result['new_ids']);
                    $allChangedIds  = array_merge($allChangedIds, $result['changed_ids']);
                    $allRelistedIds = array_merge($allRelistedIds, $result['relisted_ids']);
                    $logger->info(sprintf('Switch 2: processed %s item(s)', $this->importer->getImportedCount()));
                }

                // Mark records not seen this run as delisted
                $newlyDelisted = $this->repoDataSourceRaw->markDelistedBeforeDate($sourceId, $importStart);

                if ($newlyDelisted->count() > 0) {
                    $logger->info('Newly delisted: '.$newlyDelisted->count().' item(s)');

                    $delistedLinkIds = $newlyDelisted->pluck('link_id')->filter()->toArray();

                    // Mark corresponding parsed records as delisted
                    $this->repoDataSourceParsed->markDelistedByLinkIds($sourceId, $delistedLinkIds);

                    // Update linked games and write audit log
                    foreach ($newlyDelisted as $rawRecord) {
                        $this->repoImportLog->create(
                            $sourceId,
                            $rawRecord->link_id,
                            $rawRecord->title,
                            $rawRecord->game_id,
                            DataSourceImportLog::EVENT_DELISTED,
                            $runId
                        );

                        if ($rawRecord->game_id) {
                            $game = Game::find($rawRecord->game_id);
                            if ($game && $game->isActive()) {
                                $game->game_status = \App\Enums\GameStatus::DELISTED;
                                if ($game->format_digital === Game::FORMAT_AVAILABLE) {
                                    $game->format_digital = Game::FORMAT_DELISTED;
                                }
                                $game->save();
                                $logger->info('Delisted game ID '.$game->id.': '.$game->title);
                            }
                        }
                    }

                    // Mark delisted raw records
                    $newlyDelisted->each(function($r) { $r->is_delisted = 1; $r->save(); });
                }

                // Log re-listed items as conflicts
                if (!empty($allRelistedIds)) {
                    $relistedRecords = $this->repoDataSourceRaw->getByIds($allRelistedIds);
                    $logger->info('Re-listed (conflict): '.$relistedRecords->count().' item(s)');
                    foreach ($relistedRecords as $rawRecord) {
                        $this->repoImportLog->create(
                            $sourceId,
                            $rawRecord->link_id,
                            $rawRecord->title,
                            $rawRecord->game_id,
                            DataSourceImportLog::EVENT_CONFLICT,
                            $runId
                        );
                        $logger->warning('Conflict — re-listed item: '.$rawRecord->link_id.' ('.$rawRecord->title.')');
                    }
                }

            }

            // Parse new and changed records only
            $parseIds = array_unique(array_merge($allNewIds, $allChangedIds));
            $logger->info('Parsing '.count($parseIds).' new/changed item(s)...');

            $this->parser->setLogger($logger);
            $parsedItemCount = 0;
            $rawItemsToParse = $this->repoDataSourceRaw->getByIds($parseIds);

            foreach ($rawItemsToParse as $rawItem) {
                $this->parser->setDataSourceRaw($rawItem);
                $parsedItem = $this->parser->parseItem();
                $parsedItem->save();

                if (in_array($rawItem->id, $allNewIds)) {
                    $this->repoImportLog->create($sourceId, $rawItem->link_id, $rawItem->title, $parsedItem->game_id, DataSourceImportLog::EVENT_ADDED, $runId);
                } else {
                    // Only log as updated when parsed fields actually changed
                    $changedFields = $this->parser->getChangedFields();
                    if ($changedFields !== null) {
                        $this->repoImportLog->create($sourceId, $rawItem->link_id, $rawItem->title, $parsedItem->game_id, DataSourceImportLog::EVENT_UPDATED, $runId, $changedFields);
                    }
                }

                $parsedItemCount++;
            }

            $logger->info('Parsing complete. Parsed '.$parsedItemCount.' item(s).');

            // Link games
            $logger->info('Updating game links...');
            $this->repoDataSourceParsed->updateNintendoCoUkGameIds();
            $logger->info('Linking complete');

            $this->repoImportRun->completeRun($run);

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            $this->repoImportRun->failRun($run);
        }
    }
}
