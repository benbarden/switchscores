<?php


namespace App\Services\DataSources\Queries;

use App\DataSourceParsed;
use App\DataSource;

use Illuminate\Support\Facades\DB;

class Differences
{
    public function getReleaseDateEUNintendoCoUk($countOnly = false)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $importRulesTable = 'game_import_rules_eshop';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'eu', $countOnly);
    }

    public function getReleaseDateEUWikipedia($countOnly = false)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'eu', $countOnly);
    }

    public function getReleaseDateUSWikipedia($countOnly = false)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'us', $countOnly);
    }

    public function getReleaseDateJPWikipedia($countOnly = false)
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        $importRulesTable = 'game_import_rules_wikipedia';
        return $this->getReleaseDate($sourceId, $importRulesTable, 'jp', $countOnly);
    }

    public function getReleaseDate($sourceId, $importRulesTable, $region, $countOnly = false)
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

        if ($countOnly) {
            $selectSql = 'count(*) AS count';
        } else {
            $selectSql = 'g.id, g.title, g.link_title, '.$gameDateField.', '.$dspDateField;
        }

        return DB::select('
            SELECT '.$selectSql.'
            FROM games g
            JOIN data_source_parsed dsp ON g.id = dsp.game_id
            LEFT JOIN '.$importRulesTable.' gir ON g.id = gir.game_id
            WHERE dsp.source_id = ?
            AND (gir.'.$dateIgnoreField.' IS NULL OR gir.'.$dateIgnoreField.' = 0)
            AND '.$gameDateField.' != '.$dspDateField.'
        ', [$sourceId]);
    }
}