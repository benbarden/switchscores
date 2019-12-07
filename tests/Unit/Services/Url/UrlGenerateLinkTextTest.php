<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\UrlService;

class UrlGenerateLinkTextTest extends TestCase
{
    /**
     * @var UrlService
     */
    private $urlService;

    public function setUp(): void
    {
        parent::setUp();
        $this->urlService = new UrlService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->urlService);
    }

    public function testSimpleText()
    {
        $title = 'Super Mario Odyssey';
        $expected = 'super-mario-odyssey';

        $linkText = $this->urlService->generateLinkText($title);
        $this->assertEquals($expected, $linkText);
    }
}
