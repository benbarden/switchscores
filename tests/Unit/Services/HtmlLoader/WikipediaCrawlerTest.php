<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\Crawler as WikiCrawler;
use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

class WikipediaCrawlerTest extends TestCase
{
    /**
     * @var WikiCrawler
     */
    private $wikiCrawler;

    public function setUp()
    {
        $this->wikiCrawler = new WikiCrawler();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->wikiCrawler);

        parent::tearDown();
    }

    public function loadHtml()
    {
        $this->wikiCrawler->loadLocalData('wikipedia-games-list-180602.html');
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

        $this->wikiCrawler->loadSingleRow($row);
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
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

        $this->wikiCrawler->loadSingleRow($row);
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
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

        $this->wikiCrawler->loadSingleRow($row);
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
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

    public function testDummy()
    {
        $this->loadHtml();
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
        //print_r($tableData);
    }

    public function testTableHeaders()
    {
        $this->loadHtml();
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();

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
