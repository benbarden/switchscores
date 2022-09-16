<?php

namespace App\Domain\ReviewDraft;

use Illuminate\Support\Facades\DB;

class Stats
{
    public function getProcessStatusStats()
    {
        return DB::select('
            select process_status, count(*) AS count
            from review_drafts
            where process_status is not null
            group by process_status
        ');
    }
}