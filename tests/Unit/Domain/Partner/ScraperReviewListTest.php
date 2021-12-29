<?php

namespace Tests\Unit\Domain\Partner;

use App\Domain\Scraper\ReviewTable;
use Tests\TestCase;

class ScraperReviewListTest extends TestCase
{
    /**
     * @var ReviewTable
     */
    private $scraper;

    public function setUp(): void
    {
        $this->scraper = new ReviewTable();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->scraper);

        parent::tearDown();
    }

    public function testLoadUrlPocketTactics()
    {
        $this->scraper->crawlPage('https://www.pockettactics.com/best-mobile-games-2021');
        $this->scraper->extractRows('review-data');
        $tableData = $this->scraper->getTableData();

        $this->assertNotEmpty($tableData);

        $this->scraper->removeHeaderRow();
        $tableData = $this->scraper->getTableData();

        // 0: Title
        // 1: Date
        // 2: Rating
    }

    public function testLoadUrlNWR()
    {
        $this->scraper->crawlPage('https://www.nintendoworldreport.com/review/');
        $this->scraper->extractRows('results');
        $tableData = $this->scraper->getTableData();

        $this->assertNotEmpty($tableData);

        $this->scraper->removeHeaderRow();
        $tableData = $this->scraper->getTableData();

        // 0: Title
        // 1: Platform
        // 2: Sub-platform
        // 3: Author
        // 4: Date
        // 5: Score
    }
}
