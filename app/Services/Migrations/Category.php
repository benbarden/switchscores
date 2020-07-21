<?php

namespace App\Services\Migrations;

use Illuminate\Support\Facades\DB;

use App\DataSource;

class Category
{
    public function getGamesWithOneGenre()
    {
        $games = DB::table('games')
            ->join('data_source_parsed', 'games.id', '=', 'data_source_parsed.game_id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'data_source_parsed.genres_json', 'categories.name')
            ->where('data_source_parsed.source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->whereNull('categories.id')
            ->whereRaw('JSON_LENGTH(data_source_parsed.genres_json) = 1')
            ->orderBy('data_source_parsed.genres_json', 'asc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithOneGenre()
    {
        $games = DB::select('
            SELECT count(*) AS count
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN categories c ON g.category_id = c.id
            WHERE dsp.source_id = ?
            AND c.id IS NULL
            AND JSON_LENGTH(dsp.genres_json) = 1
            ORDER BY genres_json, g.id
        ', [DataSource::DSID_NINTENDO_CO_UK]);

        return $games[0]->count;
    }

    public function getGamesWithNamedGenreAndOneOther($genre)
    {
        switch ($genre) {
            case 'Puzzle';
                break;
            default:
                throw new \Exception('Failed to match '.$genre);
        }
        $games = DB::table('games')
            ->join('data_source_parsed', 'games.id', '=', 'data_source_parsed.game_id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'data_source_parsed.genres_json', 'categories.name')
            ->where('data_source_parsed.source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->whereNull('categories.id')
            ->whereRaw('JSON_LENGTH(data_source_parsed.genres_json) < 3')
            ->whereRaw('JSON_CONTAINS(data_source_parsed.genres_json, \'["'.$genre.'"]\')')
            ->orderBy('data_source_parsed.genres_json', 'asc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNamedGenreAndOneOther($genre)
    {
        $games = DB::select('
            SELECT count(*) AS count
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN categories c ON g.category_id = c.id
            WHERE dsp.source_id = ?
            AND c.id IS NULL
            AND JSON_LENGTH(dsp.genres_json) < 3
            AND JSON_CONTAINS(dsp.genres_json, \'["Puzzle"]\')
            ORDER BY genres_json, g.id
        ', [DataSource::DSID_NINTENDO_CO_UK]);

        return $games[0]->count;
    }
}