<?php

namespace App\Console\Commands\WeeklyBatch;

use App\Domain\WeeklyBatch\LqPublisherRecorder;
use App\Models\WeeklyBatchItem;
use Illuminate\Console\Command;

/**
 * Creates LQ games companies for the publishers of weekly update items marked LQ in past
 * batches, where the publisher isn't in the DB (#198). From #198 on this happens when the item
 * is marked LQ; this catches up the batches before it (Ben, 2026-10-24: add them as LQ).
 *
 * Same rules as LqPublisherRecorder: existing companies are never touched, and a name whose link
 * title is already used by another company is reported, not created.
 *
 * DRY RUN BY DEFAULT. Meant to be run once by hand on prod.
 */
class CreateLqPublishers extends Command
{
    protected $signature = 'WeeklyBatchCreateLqPublishers
                            {--apply : Create the companies. Without it, nothing is written}';

    protected $description = 'Creates LQ games companies for past LQ weekly update items whose publisher is not in the DB. Dry run unless --apply.';

    public function handle(LqPublisherRecorder $recorder)
    {
        $apply = (bool) $this->option('apply');

        $counts = WeeklyBatchItem::where('item_status', WeeklyBatchItem::STATUS_LOW_QUALITY)
            ->whereNotNull('publisher_normalised')
            ->where('publisher_normalised', '!=', '')
            ->get()
            ->countBy('publisher_normalised')
            ->sortKeys();

        $report = [];
        $created = $skipped = 0;
        foreach ($counts as $name => $itemCount) {
            $result = $recorder->recordPublisher($name, !$apply);
            if ($result['outcome'] === LqPublisherRecorder::EXISTS) {
                continue;
            }

            if ($result['outcome'] === LqPublisherRecorder::CREATED) {
                $created++;
                $note = $apply ? 'Created (id '.$result['company']->id.')' : 'Would create';
            } else {
                $skipped++;
                $note = 'Skipped: link title used by '.$result['company']->name.' (id '.$result['company']->id.')';
            }
            $report[] = [$name, $itemCount, $note];
        }

        $this->table(['Publisher', 'LQ items', 'Result'], $report);

        $this->newLine();
        $this->info(str_pad($apply ? 'Created:' : 'Would create:', 18).$created);
        $this->info(str_pad('Skipped:', 18).$skipped);

        $this->newLine();
        if ($apply) {
            $this->info('Applied. These publishers\' future games are confirmed LQ at the weekly update fetch.');
        } else {
            $this->warn('DRY RUN - nothing written. Pass --apply to create the companies.');
        }
    }
}
