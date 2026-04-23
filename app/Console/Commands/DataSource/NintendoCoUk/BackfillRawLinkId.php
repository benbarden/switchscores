<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use App\Models\DataSourceRaw;
use App\Models\DataSource;

class BackfillRawLinkId extends Command
{
    protected $signature = 'DSNintendoCoUkBackfillRawLinkId';

    protected $description = 'Backfills link_id on data_source_raw records from fs_id in source_data_json.';

    public function handle()
    {
        $records = DataSourceRaw::where('source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->whereNull('link_id')
            ->get();

        $this->info('Records to process: '.$records->count());

        $updated = 0;
        $skipped = 0;

        foreach ($records as $record) {
            $data = json_decode($record->source_data_json, true);
            $fsId = $data['fs_id'] ?? null;

            if ($fsId) {
                $record->link_id = $fsId;
                $record->save();
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->info('Updated: '.$updated);
        $this->info('Skipped (no fs_id): '.$skipped);
    }
}
