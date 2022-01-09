<?php

namespace App\Services\Migrations;

use App\Game;
use App\Models\DataSource;
use Illuminate\Support\Facades\DB;

class Category
{
    public function getGamesWithEshopDataAndNoCategory()
    {
        $games = DB::table('games')
            ->leftJoin('data_source_parsed', 'games.id', '=', 'data_source_parsed.game_id')
            ->select('games.*', 'data_source_parsed.genres_json')
            ->where('data_source_parsed.source_id', DataSource::DSID_NINTENDO_CO_UK)
            ->whereNull('games.category_id')
            ->orderBy('data_source_parsed.genres_json', 'asc');

        $games = $games->get();
        return $games;
    }

    public function getGamesWithNoCategory($year = null)
    {
        $year = (int) $year;

        $games = Game::whereNull('category_id');
        if ($year) {
            $games = $games->where('games.release_year', $year);
        }

        $games = $games->orderBy('games.eu_release_date', 'asc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNoCategory($year = null)
    {
        $year = (int) $year;

        if ($year) {
            $whereYearSql = 'AND g.release_year = ?';
            $params = [$year];
        } else {
            $whereYearSql = '';
            $params = [];
        }

        $games = DB::select('
            SELECT count(*) AS count
            FROM games g
            WHERE g.category_id IS NULL
            '.$whereYearSql, $params);

        return $games[0]->count;
    }

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