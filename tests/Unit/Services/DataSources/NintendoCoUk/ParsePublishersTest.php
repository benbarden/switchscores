<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Tests\TestCase;

class ParsePublishersTest extends TestCase
{
    public function makeParser($data)
    {
        $jsonData = json_encode($data);
        $dsRaw = new DataSourceRaw(['source_id' => 1, 'title' => 'Test', 'source_data_json' => $jsonData]);
        $parser = new Parser();
        $parser->setDataSourceRaw($dsRaw);
        return $parser;
    }

    public function testParseCoLtdKairosoft()
    {
        $data = ['publisher' => 'Kairosoft Co.,Ltd'];
        $parser = $this->makeParser($data);
        $expected = 'Kairosoft';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCoLtd2ZombieGames()
    {
        $data = ['publisher' => '2 Zombie Games Co., Ltd.'];
        $parser = $this->makeParser($data);
        $expected = '2 Zombie Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCoLtdTomCreate()
    {
        $data = ['publisher' => 'TOM CREATE CO.,LTD.'];
        $parser = $this->makeParser($data);
        $expected = 'TOM CREATE';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCoLtdCapcom()
    {
        $data = ['publisher' => 'CAPCOM CO., LTD.'];
        $parser = $this->makeParser($data);
        $expected = 'CAPCOM';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCoLtdRisingWinTech()
    {
        $data = ['publisher' => 'Rising Win Tech. CO., LTD'];
        $parser = $this->makeParser($data);
        $expected = 'Rising Win Tech';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLtdChequeredInk()
    {
        $data = ['publisher' => 'Chequered Ink Ltd.'];
        $parser = $this->makeParser($data);
        $expected = 'Chequered Ink';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLtdBrainSeal()
    {
        $data = ['publisher' => 'Brain Seal Ltd'];
        $parser = $this->makeParser($data);
        $expected = 'Brain Seal';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLtdHypetrainDigital()
    {
        $data = ['publisher' => 'HYPETRAIN DIGITAL LTD'];
        $parser = $this->makeParser($data);
        $expected = 'HYPETRAIN DIGITAL';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLtd8Floor()
    {
        $data = ['publisher' => '8FLOOR LTD'];
        $parser = $this->makeParser($data);
        $expected = '8FLOOR';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLtdSquareEnix()
    {
        $data = ['publisher' => 'SQUARE ENIX LTD.'];
        $parser = $this->makeParser($data);
        $expected = 'SQUARE ENIX';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseLimitedTacsGames()
    {
        $data = ['publisher' => 'TACS GAMES LIMITED'];
        $parser = $this->makeParser($data);
        $expected = 'TACS GAMES';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseAskAnEnemyStudios()
    {
        $data = ['publisher' => 'Ask An Enemy Studios, LLC'];
        $parser = $this->makeParser($data);
        $expected = 'Ask An Enemy Studios';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseDispatchGames()
    {
        $data = ['publisher' => 'Dispatch Games LLC'];
        $parser = $this->makeParser($data);
        $expected = 'Dispatch Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseTinyBuild()
    {
        $data = ['publisher' => 'tinyBuild LLC'];
        $parser = $this->makeParser($data);
        $expected = 'tinyBuild';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseClickteam()
    {
        $data = ['publisher' => 'ClickteamLLC'];
        $parser = $this->makeParser($data);
        $expected = 'Clickteam';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCatastropheGames()
    {
        $data = ['publisher' => 'CAT-astrophe Games'];
        $parser = $this->makeParser($data);
        $expected = 'CAT-astrophe Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseTheQuantumAstrophysicistsGuild()
    {
        $data = ['publisher' => 'The Quantum Astrophysicists Guild, Incorporated'];
        $parser = $this->makeParser($data);
        $expected = 'The Quantum Astrophysicists Guild';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseKonamiDigitalEntertainment()
    {
        $data = ['publisher' => 'Konami Digital Entertainment, Inc.'];
        $parser = $this->makeParser($data);
        $expected = 'Konami Digital Entertainment';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseNISAmerica()
    {
        $data = ['publisher' => 'NIS America, Inc'];
        $parser = $this->makeParser($data);
        $expected = 'NIS America';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseGameGrumps()
    {
        $data = ['publisher' => 'Game Grumps, inc'];
        $parser = $this->makeParser($data);
        $expected = 'Game Grumps';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseDevolverDigital()
    {
        $data = ['publisher' => 'Devolver Digital Inc.'];
        $parser = $this->makeParser($data);
        $expected = 'Devolver Digital';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseBethesda()
    {
        $data = ['publisher' => 'Bethesda®'];
        $parser = $this->makeParser($data);
        $expected = 'Bethesda';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseZerounoGamesDigital()
    {
        $data = ['publisher' => 'Zerouno Games Digital, S.L.'];
        $parser = $this->makeParser($data);
        $expected = 'Zerouno Games Digital';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParsePetoonsStudio()
    {
        $data = ['publisher' => 'Petoons Studio S.L.'];
        $parser = $this->makeParser($data);
        $expected = 'Petoons Studio';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseHeartbitInteractive()
    {
        $data = ['publisher' => 'Heartbit Interactive S.r.l.'];
        $parser = $this->makeParser($data);
        $expected = 'Heartbit Interactive';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseAtypicalGames()
    {
        $data = ['publisher' => 'ATYPICAL GAMES S.R.L.'];
        $parser = $this->makeParser($data);
        $expected = 'ATYPICAL GAMES';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParse34BigThings()
    {
        $data = ['publisher' => '34BigThings srl'];
        $parser = $this->makeParser($data);
        $expected = '34BigThings';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseBlueBrainGames()
    {
        $data = ['publisher' => 'Blue Brain Games, s.r.o.'];
        $parser = $this->makeParser($data);
        $expected = 'Blue Brain Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseHexage()
    {
        $data = ['publisher' => 'Hexage s.r.o.'];
        $parser = $this->makeParser($data);
        $expected = 'Hexage';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseNavilaSoftwareJapan()
    {
        $data = ['publisher' => 'Navila Software Japan G.K.'];
        $parser = $this->makeParser($data);
        $expected = 'Navila Software Japan';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseIcebergInteractive()
    {
        $data = ['publisher' => 'Iceberg Interactive B.V.'];
        $parser = $this->makeParser($data);
        $expected = 'Iceberg Interactive';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseBigosaur()
    {
        $data = ['publisher' => 'Bigosaur d.o.o.'];
        $parser = $this->makeParser($data);
        $expected = 'Bigosaur';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseBlowfishStudios()
    {
        $data = ['publisher' => 'Blowfish Studios Pty Ltd'];
        $parser = $this->makeParser($data);
        $expected = 'Blowfish Studios';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseSurpriseAttack()
    {
        $data = ['publisher' => 'Surprise Attack Pty. Ltd.'];
        $parser = $this->makeParser($data);
        $expected = 'Surprise Attack';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseSkobbejakGames()
    {
        $data = ['publisher' => 'Skobbejak Games (Pty.) Ltd.'];
        $parser = $this->makeParser($data);
        $expected = 'Skobbejak Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseScrewtapeStudios()
    {
        $data = ['publisher' => 'Screwtape Studios PTY LTD'];
        $parser = $this->makeParser($data);
        $expected = 'Screwtape Studios';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseYakAndCo()
    {
        $data = ['publisher' => 'YAK & CO PTY LTD'];
        $parser = $this->makeParser($data);
        $expected = 'YAK & CO';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCasualGames()
    {
        $data = ['publisher' => 'Casual Games FK AB'];
        $parser = $this->makeParser($data);
        $expected = 'Casual Games';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseTHQNordic()
    {
        $data = ['publisher' => 'THQ Nordic GmbH'];
        $parser = $this->makeParser($data);
        $expected = 'THQ Nordic';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParsePlanetEntertainment()
    {
        $data = ['publisher' => 'PLANET ENTMT'];
        $parser = $this->makeParser($data);
        $expected = 'PLANET Entertainment';
        $this->assertEquals($expected, $parser->parsePublishers());
    }

    public function testParseCircleEntertainment()
    {
        $data = ['publisher' => 'CIRCLE Ent.'];
        $parser = $this->makeParser($data);
        $expected = 'CIRCLE Entertainment';
        $this->assertEquals($expected, $parser->parsePublishers());
    }
}
