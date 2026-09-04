<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Importer;

/**
 * Re-derives console_id on Nintendo.co.uk source records from their system_type.
 *
 * The importer used to match system_type as an exact string, handling "nintendoswitch2"
 * and "nintendoswitch2,nintendoswitch2" but nothing else - so records where Nintendo
 * repeated the value three or four times, or mixed it with a Switch 1 value, were stored
 * as Switch 1. Importer::consoleIdFromSystemType() now decides this properly.
 *
 * A backfill is needed because the importer only rewrites console_id when a record's
 * content_hash changes: a stable record would keep the wrong console indefinitely.
 *
 * Reports by default and changes nothing. Pass --apply to write.
 */
class FixSwitch2Console extends Command
{
    protected $signature = 'DSNintendoCoUkFixSwitch2Console {--apply : Write the changes; without this the command only reports}';

    protected $description = 'Re-derives console_id on Nintendo.co.uk raw and parsed records from system_type.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply ? 'APPLYING changes.' : 'Dry run - nothing will be written. Pass --apply to write.');
        $this->line('');

        $raw = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)->get();

        $this->info('Raw records to check: '.$raw->count());

        $toFix = [];

        foreach ($raw as $record) {
            $data = json_decode($record->source_data_json, true);
            $systemType = $data['system_type'][0] ?? null;

            if ($systemType === null) {
                continue;
            }

            $correctConsoleId = Importer::consoleIdFromSystemType($systemType);

            if ((int) $record->console_id === $correctConsoleId) {
                continue;
            }

            $toFix[] = [
                'record'      => $record,
                'system_type' => $systemType,
                'url'         => $data['url'] ?? null,
                'from'        => (int) $record->console_id,
                'to'          => $correctConsoleId,
            ];
        }

        if (!$toFix) {
            $this->info('Nothing to fix.');
            return 0;
        }

        $this->line('');
        $this->warn('Records with the wrong console: '.count($toFix));
        $this->line('');

        $this->table(
            ['Link id', 'From', 'To', 'Title', 'system_type'],
            array_map(fn($row) => [
                $row['record']->link_id,
                $this->consoleLabel($row['from']),
                $this->consoleLabel($row['to']),
                mb_substr((string) $row['record']->title, 0, 40),
                mb_substr($row['system_type'], 0, 50),
            ], $toFix)
        );

        // The store URL is an independent check on system_type. If it disagrees, say so
        // rather than quietly trusting one signal over the other.
        $urlDisagrees = array_filter(
            $toFix,
            fn($row) => $row['to'] === Console::ID_SWITCH_2
                && $row['url']
                && stripos($row['url'], 'switch-2') === false
        );

        if ($urlDisagrees) {
            $this->line('');
            $this->error('Store URL does not agree for '.count($urlDisagrees).' record(s) - check these by hand:');
            foreach ($urlDisagrees as $row) {
                $this->line('  link '.$row['record']->link_id.'  '.$row['url']);
            }
        }

        $linkIds = array_values(array_filter(array_map(fn($row) => $row['record']->link_id, $toFix)));

        $parsed = DataSourceParsed::whereIn('link_id', $linkIds)
            ->where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->get();

        $this->line('');
        $this->info('Matching parsed records: '.$parsed->count());

        // games.console_id is deliberately left alone: it is maintained separately and a
        // source record is not authoritative over it. Report the disagreements instead.
        $linkedGameIds = $parsed->whereNotNull('game_id')->pluck('game_id')->all();
        $mismatched = $linkedGameIds
            ? \App\Models\Game::whereIn('id', $linkedGameIds)->where('console_id', '!=', Console::ID_SWITCH_2)->get()
            : collect();

        $this->info('Parsed records linked to a game: '.count($linkedGameIds));

        if ($mismatched->count() > 0) {
            $this->line('');
            $this->warn('These games stay as they are, but their console disagrees with the source record:');
            foreach ($mismatched as $game) {
                $this->line('  game '.$game->id.'  console '.$this->consoleLabel($game->console_id).'  '.$game->title);
            }
        }

        if (!$apply) {
            $this->line('');
            $this->info('Dry run finished. Re-run with --apply to write these changes.');
            return 0;
        }

        $rawUpdated = 0;
        foreach ($toFix as $row) {
            $row['record']->console_id = $row['to'];
            $row['record']->save();
            $rawUpdated++;
        }

        $parsedUpdated = 0;
        foreach ($parsed as $parsedRecord) {
            $correct = collect($toFix)->firstWhere('record.link_id', $parsedRecord->link_id);
            if (!$correct) {
                continue;
            }
            if ((int) $parsedRecord->console_id === $correct['to']) {
                continue;
            }
            $parsedRecord->console_id = $correct['to'];
            $parsedRecord->save();
            $parsedUpdated++;
        }

        $this->line('');
        $this->info('Raw records updated:    '.$rawUpdated);
        $this->info('Parsed records updated: '.$parsedUpdated);
        $this->info('Games changed:          0 (games.console_id is never written by this command)');

        return 0;
    }

    private function consoleLabel(int $consoleId): string
    {
        return $consoleId === Console::ID_SWITCH_2 ? 'Switch 2' : 'Switch 1';
    }
}
