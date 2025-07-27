<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Tests\TestCase;

class ParsePlayersTest extends TestCase
{
    public function makeParser($data)
    {
        $jsonData = json_encode($data);
        $dsRaw = new DataSourceRaw(['source_id' => 1, 'title' => 'Test', 'source_data_json' => $jsonData]);
        $parser = new Parser();
        $parser->setDataSourceRaw($dsRaw);
        return $parser;
    }

    public function testParsePlayersDifferent()
    {
        $data = ['players_from' => 1, 'players_to' => 2];
        $parser = $this->makeParser($data);
        $expected = '1-2';

        $this->assertEquals($expected, $parser->parsePlayers());
    }

    public function testParsePlayersSame()
    {
        $data = ['players_from' => 2, 'players_to' => 2];
        $parser = $this->makeParser($data);
        $expected = '2';

        $this->assertEquals($expected, $parser->parsePlayers());
    }

    public function testParsePlayersFromNull()
    {
        $data = ['players_from' => null, 'players_to' => 2];
        $parser = $this->makeParser($data);
        $expected = '1-2';

        $this->assertEquals($expected, $parser->parsePlayers());
    }

    public function testParsePlayersFromNullSame()
    {
        $data = ['players_from' => null, 'players_to' => 1];
        $parser = $this->makeParser($data);
        $expected = '1';

        $this->assertEquals($expected, $parser->parsePlayers());
    }

    public function testParsePlayersFromMissing()
    {
        $data = ['players_to' => 2];
        $parser = $this->makeParser($data);
        $expected = '1-2';

        $this->assertEquals($expected, $parser->parsePlayers());
    }

    public function testParsePlayersFromMissingSame()
    {
        $data = ['players_to' => 1];
        $parser = $this->makeParser($data);
        $expected = '1';

        $this->assertEquals($expected, $parser->parsePlayers());
    }
}
