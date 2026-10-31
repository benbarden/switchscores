<?php

namespace Tests\Unit\Views;

use PHPUnit\Framework\TestCase;

/**
 * The packshot macros run on the Bootstrap 3 and 5 themes. Only Bootstrap 3 has img-responsive,
 * so a sized image needs height: auto of its own, or it keeps its height attribute and stretches.
 */
class PackshotMacrosTest extends TestCase
{
    public function testEverySizedImageSetsItsOwnHeightToAuto()
    {
        $template = file_get_contents(__DIR__.'/../../../resources/views/ui/components/game/packshots.twig');

        preg_match_all('/<img [^>]*height="\d+"[^>]*>/', $template, $images);

        $this->assertNotEmpty($images[0]);
        foreach ($images[0] as $image) {
            $this->assertStringContainsString('height: auto', $image);
        }
    }
}
