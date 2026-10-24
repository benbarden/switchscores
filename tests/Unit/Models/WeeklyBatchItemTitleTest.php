<?php

namespace Tests\Unit\Models;

use App\Models\WeeklyBatchItem;
use Tests\TestCase;

/**
 * Titles edited by hand in the weekly update (#199).
 */
class WeeklyBatchItemTitleTest extends TestCase
{
    private function item(string $title): WeeklyBatchItem
    {
        return new WeeklyBatchItem(['title' => $title, 'title_raw' => strtoupper($title)]);
    }

    public function testNotEditedByDefault()
    {
        $item = $this->item('Steins;gate Re:boot');

        $this->assertFalse($item->hasManualTitle());
        $this->assertSame('Steins;gate Re:boot', $item->autoTitle());
    }

    public function testEditKeepsTheAutoTitle()
    {
        $item = $this->item('Steins;gate Re:boot');
        $item->setManualTitle('STEINS;GATE RE:BOOT');

        $this->assertTrue($item->hasManualTitle());
        $this->assertSame('STEINS;GATE RE:BOOT', $item->title);
        $this->assertSame('Steins;gate Re:boot', $item->autoTitle());
    }

    public function testSecondEditKeepsTheOriginalAutoTitle()
    {
        $item = $this->item('Ea Sports FC 27');
        $item->setManualTitle('EA SPORTS FC 27');
        $item->setManualTitle('EA Sports FC 27');

        $this->assertSame('EA Sports FC 27', $item->title);
        $this->assertSame('Ea Sports FC 27', $item->title_auto);
    }

    public function testEditingBackToTheAutoTitleClearsTheEdit()
    {
        $item = $this->item('Ea Sports FC 27');
        $item->setManualTitle('EA Sports FC 27');
        $item->setManualTitle('Ea Sports FC 27');

        $this->assertFalse($item->hasManualTitle());
        $this->assertSame('Ea Sports FC 27', $item->title);
    }
}
