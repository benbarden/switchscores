<?php

namespace Tests\Unit\Services\Url;

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
    private $serviceUrl;

    public function setUp(): void
    {
        parent::setUp();
        $this->serviceUrl = new UrlService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->serviceUrl);
    }

    public function testSimpleText()
    {
        $title = 'Super Mario Odyssey';
        $expected = 'super-mario-odyssey';

        $linkText = $this->serviceUrl->generateLinkText($title);
        $this->assertEquals($expected, $linkText);
    }
}
