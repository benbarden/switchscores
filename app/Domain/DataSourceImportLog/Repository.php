<?php

namespace App\Domain\DataSourceImportLog;

use App\Models\DataSourceImportLog;

class Repository
{
    public function create($sourceId, $linkId, $gameId, $eventType, $runId = null, $changedFields = null)
    {
        $log = new DataSourceImportLog();
        $log->run_id        = $runId;
        $log->source_id     = $sourceId;
        $log->link_id       = $linkId;
        $log->game_id       = $gameId;
        $log->event_type    = $eventType;
        $log->changed_fields = $changedFields;
        $log->created_at    = now();
        $log->save();
        return $log;
    }

    public function getCountsByRunId($runId): array
    {
        $rows = DataSourceImportLog::where('run_id', $runId)
            ->selectRaw('event_type, count(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type')
            ->toArray();

        return [
            'added'    => $rows[DataSourceImportLog::EVENT_ADDED]    ?? 0,
            'updated'  => $rows[DataSourceImportLog::EVENT_UPDATED]   ?? 0,
            'delisted' => $rows[DataSourceImportLog::EVENT_DELISTED]  ?? 0,
            'conflict' => $rows[DataSourceImportLog::EVENT_CONFLICT]  ?? 0,
        ];
    }

    public function getCountsByRunIds(array $runIds): array
    {
        $rows = DataSourceImportLog::whereIn('run_id', $runIds)
            ->selectRaw('run_id, event_type, count(*) as total')
            ->groupBy('run_id', 'event_type')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row->run_id][$row->event_type] = $row->total;
        }
        return $counts;
    }

    public function getByRunId($runId)
    {
        return DataSourceImportLog::where('run_id', $runId)
            ->orderBy('event_type')
            ->orderBy('created_at')
            ->get();
    }

    public function getRecentBySource($sourceId, $limit = 100)
    {
        return DataSourceImportLog::where('source_id', $sourceId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
