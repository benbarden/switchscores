<?php

namespace Tests\Unit\Services\DataSources\Wikipedia;

use App\Models\DataSourceRaw;
use App\Services\DataSources\Wikipedia\Parser;
use Tests\TestCase;

class ParseDevelopersTest extends TestCase
{
    public function makeParser($data)
    {
        $jsonData = json_encode($data);
        $dsRaw = new DataSourceRaw(['source_id' => 1, 'title' => 'Test', 'source_data_json' => $jsonData]);
        $parser = new Parser($dsRaw);
        return $parser;
    }

    public function testParseNull()
    {
        $data = [];
        $parser = $this->makeParser($data);
        $expected = null;

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseSingleWord()
    {
        $data = ['developers' => 'Imaginary Game Creations'];
        $parser = $this->makeParser($data);
        $expected = 'Imaginary Game Creations';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseTwoWordsInSequence()
    {
        $data = ['developers' => 'Apple Studios,Banana Bonanza'];
        $parser = $this->makeParser($data);
        $expected = 'Apple Studios,Banana Bonanza';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseThreeWordsNotInSequence()
    {
        $data = ['developers' => 'Banana Bonanza,Croissant Creatives,Apple Studios'];
        $parser = $this->makeParser($data);
        $expected = 'Apple Studios,Banana Bonanza,Croissant Creatives';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseArrayAsInput()
    {
        $data = ['developers' => ['Banana Bonanza','Croissant Creatives','Apple Studios']];
        $parser = $this->makeParser($data);
        $expected = 'Apple Studios,Banana Bonanza,Croissant Creatives';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseSonicManiaDevelopers()
    {
        $data = ['developers' => 'PagodaWest Games,Tantalus Media,Headcannon'];
        $parser = $this->makeParser($data);
        $expected = 'Headcannon,PagodaWest Games,Tantalus Media';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }

    public function testParseTrimmingSpaces()
    {
        $data = ['developers' => ' PagodaWest Games, Tantalus Media , Headcannon'];
        $parser = $this->makeParser($data);
        $expected = 'Headcannon,PagodaWest Games,Tantalus Media';

        $this->assertEquals($expected, $parser->parseDevelopers());
    }
}
