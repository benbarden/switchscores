<?php

namespace App\Domain\Audit;

use Illuminate\Support\Collection;
use OwenIt\Auditing\Models\Audit;

class Repository
{
    public function getAll($limit = 250)
    {
        $auditList = Audit::orderBy('id', 'desc');
        if ($limit) {
            $auditList = $auditList->limit($limit);
        }
        return $auditList->get();
    }

    public function getGame($limit = 250)
    {
        $auditList = Audit::
        where('auditable_type', 'App\Models\Game')
            ->orderBy('id', 'desc');
        if ($limit) {
            $auditList = $auditList->limit($limit);
        }
        return $auditList->get();
    }

    public function getReviewLink($limit = 250)
    {
        $auditList = Audit::
        where('auditable_type', 'App\Models\ReviewLink')
            ->orderBy('id', 'desc');
        if ($limit) {
            $auditList = $auditList->limit($limit);
        }
        return $auditList->get();
    }

    public function getGamesCompany($limit = 250)
    {
        $auditList = Audit::where('auditable_type', 'App\Models\GamesCompany')
            ->orderBy('id', 'desc');
        if ($limit) {
            $auditList = $auditList->limit($limit);
        }
        return $auditList->get();
    }

    public function getAggregatedGameAudits($gameId, $limit = 25)
    {
        /*
         * For reference, here's how we get a game's audits
        $gameAuditsCore = $game->audits()->orderBy('id', 'desc')->get();
        */

        $aggAudits = new Collection();

        // App\Models\Game
        $audits = Audit::where('auditable_type', 'App\Models\Game')->where('auditable_id', $gameId)->orderBy('id', 'desc')->get();
        foreach ($audits as $audit) {
            $aggAudits->push($audit);
        }

        $aggAudits = $aggAudits->sortByDesc('id');

        if ($aggAudits->count() > $limit) {
            $aggAudits = $aggAudits->take($limit);
        }

        return $aggAudits;
    }
}