<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Tests\TestCase;

class ParseReleaseDateTest extends TestCase
{
    public function makeParser($data)
    {
        $jsonData = json_encode($data);
        $dsRaw = new DataSourceRaw(['source_id' => 1, 'title' => 'Test', 'source_data_json' => $jsonData]);
        $parser = new Parser($dsRaw);
        return $parser;
    }

    public function testParseReleaseDateNull()
    {
        $data = [];
        $parser = $this->makeParser($data);
        $expected = null;

        $this->assertEquals($expected, $parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateReal()
    {
        $data = ['pretty_date_s' => '07/08/2020'];
        $parser = $this->makeParser($data);
        $expected = '2020-08-07';

        $this->assertEquals($expected, $parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateYearOnly()
    {
        $data = ['pretty_date_s' => '2020'];
        $parser = $this->makeParser($data);
        $expected = null;

        $this->assertEquals($expected, $parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateInvalidData()
    {
        $data = ['pretty_date_s' => 'December 2017'];
        $parser = $this->makeParser($data);
        $expected = null;

        $this->assertEquals($expected, $parser->parseReleaseDate($data));
    }
}
