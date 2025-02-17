<?php

namespace App\Domain\GameCollection;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function collectionCategoryStats($collectionId)
    {
        // Most if not all games should have a category.
        // We'll prioritise categories for all games in a collection. So, we shouldn't have any nulls here.
        return DB::select("
            select g.category_id, c.name, c.link_title, count(*) as count
            from games g
            join categories c on g.category_id = c.id
            where g.collection_id = ? group by g.category_id order by count(*) desc
        ", [$collectionId]);
    }

    public function collectionSeriesStats($collectionId)
    {
        // Most games won't have a series, but the full list should be visible in the category breakdown.
        // Hence, no null values.
        return DB::select("
            select g.series_id, s.series, s.link_title, count(*) as count
            from games g
            join game_series s on g.series_id = s.id
            where g.collection_id = ? group by g.series_id order by s.series asc
        ", [$collectionId]);
    }
}