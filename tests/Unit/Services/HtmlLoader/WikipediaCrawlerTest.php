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

    public function loadPageOne()
    {
        $this->wikiCrawler->loadLocalData('wikipedia-games-1.html');
    }

    public function loadPageTwo()
    {
        $this->wikiCrawler->loadLocalData('wikipedia-games-2.html');
    }

    /**
     * Used to test the parsing of a single row of data.
     * We do this before loading the full table so we can fail early if something goes awry.
     */
    public function testRowTheLongest5Minutes()
    {
        $row = <<<tableRow
<tr>
<th scope="row"><i>The Longest 5 Minutes</i></th>
<td><a href="/wiki/Role-playing_video_game" title="Role-playing video game">Role-playing</a></td>
<td>Syupro-DX</td>
<td><a href="/wiki/NIS_America" class="mw-redirect" title="NIS America">NIS America</a></td>
<td style="background:#F99;vertical-align:middle;text-align:center;" class="table-no">No</td>
<td data-sort-value="" style="background: #ececec; color: #2C2C2C; vertical-align: middle; font-size: smaller; text-align: center;" class="table-na">Unreleased</td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-13-0000</span><span style="white-space:nowrap">February 13, 2018</span></td>
<td><span class="sortkey" style="display:none;speak:none">000000002018-02-16-0000</span><span style="white-space:nowrap">February 16, 2018</span></td>
<td><sup id="cite_ref-424" class="reference"><a href="#cite_note-424">[423]</a></sup></td>
</tr>

tableRow;

        $this->wikiCrawler->loadSingleRow($row);
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = 'The Longest 5 Minutes';
        $expectedGenre = 'Role-playing';
        $expectedDev = 'Syupro-DX';
        $expectedPub = 'NIS America';
        $expectedExcls = 'No';
        $expectedRelDateJP = 'Unreleased';
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
        $this->assertEquals($expectedExcls, $rowData[4]);
        $this->assertEquals($expectedRelDateJP, $rowData[5]);
        $this->assertEquals($expectedRelDateNA, $rowData[6]);
        $this->assertEquals($expectedRelDatePAL, $rowData[7]);

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
<td>Midboss</td>
<td>Midboss</td>
<td style="background:#F99;vertical-align:middle;text-align:center;" class="table-no">No</td>
<td style="background:#DDF; color:#2C2C2C; text-align:center;"><span class="sortkey" style="display:none;speak:none">000000002018-03-31-0000</span>Q1 2018</td>
<td style="background:#DDF; color:#2C2C2C; text-align:center;"><span class="sortkey" style="display:none;speak:none">000000002018-03-31-0000</span>Q1 2018</td>
<td style="background:#DDF; color:#2C2C2C; text-align:center;"><span class="sortkey" style="display:none;speak:none">000000002018-03-31-0000</span>Q1 2018</td>
<td><sup id="cite_ref-12" class="reference"><a href="#cite_note-12">[11]</a></sup></td>
</tr>

tableRow;

        $this->wikiCrawler->loadSingleRow($row);
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
        //print_r($tableData);

        // @todo Generate a model from the data.

        $expectedTitle = '2064: Read Only Memories';
        $expectedGenre = 'Adventure';
        $expectedDev = 'Midboss';
        $expectedPub = 'Midboss';
        $expectedExcls = 'No';
        $expectedRelDateJP = [
            0 => '000000002018-03-31-0000',
            1 => 'Q1 2018',
        ];
        $expectedRelDateNA = [
            0 => '000000002018-03-31-0000',
            1 => 'Q1 2018',
        ];
        $expectedRelDatePAL = [
            0 => '000000002018-03-31-0000',
            1 => 'Q1 2018',
        ];

        $rowData = $tableData[0]; // There's only one row in this test case

        $this->assertEquals($expectedTitle, $rowData[0]);
        $this->assertEquals($expectedGenre, $rowData[1]);
        $this->assertEquals($expectedDev, $rowData[2]);
        $this->assertEquals($expectedPub, $rowData[3]);
        $this->assertEquals($expectedExcls, $rowData[4]);
        $this->assertEquals($expectedRelDateJP, $rowData[5]);
        $this->assertEquals($expectedRelDateNA, $rowData[6]);
        $this->assertEquals($expectedRelDatePAL, $rowData[7]);

    }

    public function testDummy()
    {
        $this->loadPageOne();
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();
        //print_r($tableData);
    }

    public function testTableHeaders()
    {
        $this->loadPageOne();
        $this->wikiCrawler->extractRows();
        $tableData = $this->wikiCrawler->getTableData();

        $expectedHeader0 = [
            0 => 'Title',
            1 => 'Genre(s)',
            2 => 'Developer(s)',
            3 => 'Publisher(s)',
            4 => 'Excls.',
            5 => 'Release date',
            6 => 'Ref.',
        ];

        $expectedHeader1 = [
            0 => 'Japan',
            1 => 'North America',
            2 => 'PAL',
        ];

        $this->assertEquals($expectedHeader0, $tableData[0]);
        $this->assertEquals($expectedHeader1, $tableData[1]);
    }
}
