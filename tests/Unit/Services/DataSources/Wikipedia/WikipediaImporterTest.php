<?php

namespace Tests\Unit\Services\DataSources\Wikipedia;

use App\Services\DataSources\Wikipedia\Importer;
use Illuminate\Support\Collection;
use Tests\TestCase;

class WikipediaImporterTest extends TestCase
{
    /**
     * @var Importer
     */
    private $importer;

    public function setUp(): void
    {
        $this->importer = new Importer();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->importer);

        parent::tearDown();
    }

    public function loadHtml()
    {
        $this->importer->loadLocalData('wikipedia-games-list-180602.html');
    }

    /**
     * Used to test the parsing of a single row of data.
     * We do this before loading the full table so we can fail early if something goes awry.
     */
    public function testRowTheLongest5Minutes()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i>The Longest Five Minutes</i></th>
<td><a href="/wiki/Role-playing_video_game" title="Role-playing video game">Role-playing</a></td>
<td>Syupro-DX</td>
<td><a href="/wiki/NIS_America" class="mw-redirect" title="NIS America">NIS America</a></td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-04-26-0000</span><span style="white-space:nowrap">April 26, 2018</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-13-0000</span><span style="white-space:nowrap">February 13, 2018</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-16-0000</span><span style="white-space:nowrap">February 16, 2018</span></td>
<td><sup id="cite_ref-auto2_293-0" class="reference"><a href="#cite_note-auto2-293">[293]</a></sup></td>
</tr>

tableRow;

        $this->importer->loadSingleRow($row);
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = 'The Longest Five Minutes';
        $expectedGenre = 'Role-playing';
        $expectedDev = 'Syupro-DX';
        $expectedPub = 'NIS America';
        $expectedRelDateJP = [
            0 => '000000002018-04-26-0000',
            1 => 'April 26, 2018',
        ];
        $expectedRelDateNA = [
            0 => '000000002018-02-13-0000',
            1 => 'February 13, 2018',
        ];
        $expectedRelDatePAL = [
            0 => '000000002018-02-16-0000',
            1 => 'February 16, 2018',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedRelDateJP, $rowData[4]);
        $this->assertEquals($expectedRelDateNA, $rowData[5]);
        $this->assertEquals($expectedRelDatePAL, $rowData[6]);

    }

    /**
     * Used to test the parsing of a single row of data.
     * We do this before loading the full table so we can fail early if something goes awry.
     *
     * This includes a different test case where we have one date in a span tag, and one not.
     */
    public function testRow2064ReadOnlyMemories()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i><a href="/wiki/2064:_Read_Only_Memories" title="2064: Read Only Memories">2064: Read Only Memories</a></i></th>
<td><a href="/wiki/Adventure_game" title="Adventure game">Adventure</a></td>
<td><a href="/wiki/MidBoss" title="MidBoss">MidBoss</a></td>
<td>MidBoss</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-13-31-0000</span>2018</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-13-31-0000</span>2018</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-13-31-0000</span>2018</td>
<td></td>
</tr>

tableRow;

        $this->importer->loadSingleRow($row);
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = '2064: Read Only Memories';
        $expectedGenre = 'Adventure';
        $expectedDev = 'MidBoss';
        $expectedPub = 'MidBoss';
        $expectedRelDateJP = [
            0 => '000000002018-13-31-0000',
            1 => '2018',
        ];
        $expectedRelDateNA = [
            0 => '000000002018-13-31-0000',
            1 => '2018',
        ];
        $expectedRelDatePAL = [
            0 => '000000002018-13-31-0000',
            1 => '2018',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedRelDateJP, $rowData[4]);
        $this->assertEquals($expectedRelDateNA, $rowData[5]);
        $this->assertEquals($expectedRelDatePAL, $rowData[6]);

    }

    public function test3DMiniGolf()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i>3D MiniGolf</i></th>
<td><a href="/wiki/Sports_game" title="Sports game">Sports</a></td>
<td>Joindots</td>
<td>Joindots</td>
<td>Unreleased</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-01-0000</span><span style="white-space:nowrap">February 1, 2018</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-01-0000</span><span style="white-space:nowrap">February 1, 2018</span></td>
<td><sup id="cite_ref-10" class="reference"><a href="#cite_note-10">[10]</a></sup></td>
</tr>

tableRow;

        $this->importer->loadSingleRow($row);
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = '3D MiniGolf';
        $expectedGenre = 'Sports';
        $expectedDev = 'Joindots';
        $expectedPub = 'Joindots';
        $expectedRelDateJP = 'Unreleased';
        $expectedRelDateNA = [
            0 => '000000002018-02-01-0000',
            1 => 'February 1, 2018',
        ];
        $expectedRelDatePAL = [
            0 => '000000002018-02-01-0000',
            1 => 'February 1, 2018',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedRelDateJP, $rowData[4]);
        $this->assertEquals($expectedRelDateNA, $rowData[5]);
        $this->assertEquals($expectedRelDatePAL, $rowData[6]);

    }

    public function testTheFlameInTheFlood()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i><a href="/wiki/The_Flame_in_the_Flood" title="The Flame in the Flood">The Flame in the Flood</a></i></th>
<td><a href="/wiki/Adventure_video_game" class="mw-redirect" title="Adventure video game">Adventure</a></td>
<td>The Molasses Flood</td>
<td>
<div class="hlist hlist-separated">
<ul>
<li><span style="font-size:95%;"><a href="/wiki/Japan" title="Japan">JP</a>:</span> <a href="/wiki/Teyon" title="Teyon">Teyon</a></li>
<li><span style="font-size:95%;"><abbr title="Worldwide">WW</abbr>:</span> <a href="/wiki/Curve_Digital" title="Curve Digital">Curve Digital</a></li>
</ul>
</div>
</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-03-29-0000</span><span style="white-space:nowrap">March 29, 2018</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002017-10-12-0000</span><span style="white-space:nowrap">October 12, 2017</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002017-10-12-0000</span><span style="white-space:nowrap">October 12, 2017</span></td>
<td></td>
</tr>

tableRow;

        $this->importer->loadSingleRow($row);
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = 'The Flame in the Flood';
        $expectedGenre = 'Adventure';
        $expectedDev = 'The Molasses Flood';
        $expectedPub = [
            0 => 'JP: Teyon',
            1 => 'WW: Curve Digital',
        ];
        $expectedRelDateJP = [
            0 => '000000002018-03-29-0000',
            1 => 'March 29, 2018',
        ];
        $expectedRelDateNA = [
            0 => '000000002017-10-12-0000',
            1 => 'October 12, 2017',
        ];
        $expectedRelDatePAL = [
            0 => '000000002017-10-12-0000',
            1 => 'October 12, 2017',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedRelDateJP, $rowData[4]);
        $this->assertEquals($expectedRelDateNA, $rowData[5]);
        $this->assertEquals($expectedRelDatePAL, $rowData[6]);

    }

    // This test is to find a workaround for HTML with a line break in it
    public function test9MonkeysOfShaolin()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i>9 Monkeys of Shaolin</i>
</th>
<td><a href="/wiki/Beat_%27em_up" title="Beat &#39;em up">Beat 'em up</a>
</td>
<td>Sobaka Studio
</td>
<td><a href="/wiki/1C_Company" title="1C Company">Buka Entertainment</a>
</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-12-31-0000</span>Q4 2018</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-12-31-0000</span>Q4 2018</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-12-31-0000</span>Q4 2018
</td>
<td><sup id="cite_ref-13" class="reference"><a href="#cite_note-13">&#91;13&#93;</a></sup>
</td></tr>

tableRow;

        $this->importer->loadSingleRow($row);
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = '9 Monkeys of Shaolin';
        $expectedGenre = 'Beat \'em up';
        $expectedDev = 'Sobaka Studio';
        $expectedPub = 'Buka Entertainment';
        $expectedRelDateJP = [
            0 => '000000002018-12-31-0000',
            1 => 'Q4 2018',
        ];
        $expectedRelDateNA = [
            0 => '000000002018-12-31-0000',
            1 => 'Q4 2018',
        ];
        $expectedRelDatePAL = [
            0 => '000000002018-12-31-0000',
            1 => 'Q4 2018',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedRelDateJP, $rowData[4]);
        $this->assertEquals($expectedRelDateNA, $rowData[5]);
        $this->assertEquals($expectedRelDatePAL, $rowData[6]);

    }

/*
    public function testDummy()
    {
        $this->loadHtml();
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();
        //print_r($tableData);
    }
*/

    public function testTableHeaders()
    {
        $this->loadHtml();
        $this->importer->extractRows();
        $tableData = $this->importer->getTableData();

        $expectedHeader0 = [
            0 => 'Title',
            1 => 'Genre(s)',
            2 => 'Developer(s)',
            3 => 'Publisher(s)',
            4 => 'Release date',
            5 => 'Ref.',
        ];

        $expectedHeader1 = [
            0 => 'JP',
            1 => 'NA',
            2 => 'PAL',
        ];

        $this->assertEquals($expectedHeader0, $tableData[0]);
        $this->assertEquals($expectedHeader1, $tableData[1]);
    }
}
