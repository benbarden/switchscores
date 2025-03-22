<?php

namespace Tests\Unit\Domain\Url;

use App\Domain\Url\LinkTitle;
use Tests\TestCase;

class UrlGenerateLinkTextTest extends TestCase
{
    /**
     * @var LinkTitle
     */
    private $urlLinkTitle;

    public function setUp(): void
    {
        parent::setUp();
        $this->urlLinkTitle = new LinkTitle();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->urlLinkTitle);
    }

    public function testSimpleText()
    {
        $title = 'Super Mario Odyssey';
        $expected = 'super-mario-odyssey';

        $linkText = $this->urlLinkTitle->generate($title);
        $this->assertEquals($expected, $linkText);
    }
}
