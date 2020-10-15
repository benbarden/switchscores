<?php


namespace App\Services\DataSources\Queries;

use App\DataSourceParsed;
use App\DataSource;

use Illuminate\Support\Facades\DB;

class Differences
{
    private $countOnly = false;

    public function setCountOnly($countOnly = true)
    {
        $this->countOnly = $countOnly;
    }

    public function getReleaseDateEUNintendoCoUk($gameId = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $importRulesTable = 'game_import_rules_eshop';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'eu', $gameId);
    }

    public function getPriceNintendoCoUk($gameId = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $importRulesTable = 'game_import_rules_eshop';
        return $this->getPrice($sourceId, $importRulesTable, $gameId);
    }

    public function getPlayersNintendoCoUk($gameId = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $importRulesTable = 'game_import_rules_eshop';
        return $this->getPlayers($sourceId, $importRulesTable, $gameId);
    }

    public function getPublishersNintendoCoUk($gameId = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $importRulesTable = 'game_import_rules_eshop';
        return $this->getPublishers($sourceId, $importRulesTable, $gameId);
    }

    public function getReleaseDateEUWikipedia($gameId = null)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'eu', $gameId);
    }

    public function getReleaseDateUSWikipedia($gameId = null)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'us', $gameId);
    }

    public function getReleaseDateJPWikipedia($gameId = null)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'jp', $gameId);
    }

    public function getDevelopersWikipedia($gameId = null)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getDevelopers($sourceId, $importRulesTable, $gameId);
    }

    public function getPublishersWikipedia($gameId = null)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getPublishers($sourceId, $importRulesTable, $gameId);
    }

    public function getReleaseDate($sourceId, $importRulesTable, $region, $gameId = null)
    {
        if ($region == 'eu') {

            $gameDateField = 'g.eu_release_date';
            $dspDateField = 'dsp.release_date_eu';
            $dateIgnoreField = 'ignore_europe_dates';

        } elseif ($region == 'us') {

            $gameDateField = 'g.us_release_date';
            $dspDateField = 'dsp.release_date_us';
            $dateIgnoreField = 'ignore_us_dates';

        } elseif ($region == 'jp') {

            $gameDateField = 'g.jp_release_date';
            $dspDateField = 'dsp.release_date_jp';
            $dateIgnoreField = 'ignore_jp_dates';

        } else {

            throw new \Exception('Unknown region: '.$region);

        }

        if ($this->countOnly) {
            $selectSql = 'count(*) AS count';
        } else {
            $selectSql = 'g.id, g.title, g.link_title, '.$gameDateField.', '.$dspDateField;
        }
        if ($gameId) {
            $gameSql = 'AND dsp.game_id = ?';
            $params = [$sourceId, $gameId];
        } else {
            $gameSql = '';
            $params = [$sourceId];
        }

        return DB::select('
            SELECT '.$selectSql.'
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            WHERE dsp.source_id = ?
            AND (gir.'.$dateIgnoreField.' IS NULL OR gir.'.$dateIgnoreField.' = 0)
            AND '.$gameDateField.' != '.$dspDateField.'
            '.$gameSql.'
        ', $params);
    }

    public function getPrice($sourceId, $importRulesTable, $gameId = null)
    {
        $gameField = 'g.price_eshop';
        $dspField = 'dsp.price_standard';
        $ignoreField = 'ignore_price';

        if ($this->countOnly) {
            $selectSql = 'count(*) AS count';
        } else {
            $selectSql = 'g.id, g.title, g.link_title, '.$gameField.', '.$dspField;
        }
        if ($gameId) {
            $gameSql = 'AND dsp.game_id = ?';
            $params = [$sourceId, $gameId];
        } else {
            $gameSql = '';
            $params = [$sourceId];
        }

        return DB::select('
            SELECT '.$selectSql.'
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            WHERE dsp.source_id = ?
            AND (gir.'.$ignoreField.' IS NULL OR gir.'.$ignoreField.' = 0)
            AND '.$gameField.' != '.$dspField.'
            '.$gameSql.'
        ', $params);
    }

    public function getPlayers($sourceId, $importRulesTable, $gameId = null)
    {
        $gameField = 'g.players';
        $dspField = 'dsp.players';
        $ignoreField = 'ignore_players';

        if ($this->countOnly) {
            $selectSql = 'count(*) AS count';
        } else {
            $selectSql = 'g.id, g.title, g.link_title, '.$gameField.' AS game_players, '.$dspField.' AS dsp_players';
        }
        if ($gameId) {
            $gameSql = 'AND dsp.game_id = ?';
            $params = [$sourceId, $gameId];
        } else {
            $gameSql = '';
            $params = [$sourceId];
        }

        return DB::select('
            SELECT '.$selectSql.'
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            WHERE dsp.source_id = ?
            AND (gir.'.$ignoreField.' IS NULL OR gir.'.$ignoreField.' = 0)
            AND '.$gameField.' != '.$dspField.'
            '.$gameSql.'
        ', $params);
    }

    public function getDevelopers($sourceId, $importRulesTable, $gameId = null)
    {
        if ($gameId) {
            $gameSql = 'AND dsp.game_id = ?';
            $params = [$sourceId, $gameId];
        } else {
            $gameSql = '';
            $params = [$sourceId];
        }

        return DB::select('
            SELECT g.id, g.title, g.link_title, 
            (
                SELECT GROUP_CONCAT(p.name ORDER BY p.name ASC SEPARATOR \',\')
                FROM partners p
                JOIN game_developers gdev ON p.id = gdev.developer_id
                WHERE gdev.game_id = g.id
            ) AS game_developers,
            GROUP_CONCAT(dsp.developers) AS dsp_developers
            FROM games g
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            WHERE dsp.source_id = ?
            AND (gir.ignore_developers IS NULL OR gir.ignore_developers = 0)
            '.$gameSql.'
            GROUP BY g.id
            HAVING game_developers != dsp_developers
        ', $params);
    }

    public function getPublishers($sourceId, $importRulesTable, $gameId = null)
    {
        if ($gameId) {
            $gameSql = 'AND dsp.game_id = ?';
            $params = [$sourceId, $gameId];
        } else {
            $gameSql = '';
            $params = [$sourceId];
        }

        return DB::select('
            SELECT g.id, g.title, g.link_title, 
            (
                SELECT GROUP_CONCAT(p.name ORDER BY p.name ASC SEPARATOR \',\')
                FROM partners p
                JOIN game_publishers gpub ON p.id = gpub.publisher_id
                WHERE gpub.game_id = g.id
            ) AS game_publishers,
            GROUP_CONCAT(dsp.publishers) AS dsp_publishers
            FROM games g
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            WHERE dsp.source_id = ?
            AND (gir.ignore_publishers IS NULL OR gir.ignore_publishers = 0)
            '.$gameSql.'
            GROUP BY g.id
            HAVING game_publishers != dsp_publishers
        ', $params);
    }
}