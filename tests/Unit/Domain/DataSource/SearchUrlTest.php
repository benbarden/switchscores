<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\SearchUrl;
use App\Models\Console;
use Tests\TestCase;

class SearchUrlTest extends TestCase
{
    public function testSwitch1Filter()
    {
        $this->assertSame(
            'https://www.nintendo.com/en-gb/Search/Search-299117.html?q=Dino%20Odyssey&f=147394-5-86',
            SearchUrl::forTitle('Dino Odyssey', Console::ID_SWITCH_1)
        );
    }

    public function testSwitch2Filter()
    {
        $this->assertSame(
            'https://www.nintendo.com/en-gb/Search/Search-299117.html?q=Dino%20Odyssey&f=147394-91-126551',
            SearchUrl::forTitle('Dino Odyssey', Console::ID_SWITCH_2)
        );
    }

    public function testEverySpaceIsEncodedAndTheColonGoes()
    {
        // The editor button used to send "Tri+Breaker%20%20A..." - only the first space became "+"
        $this->assertSame(
            'https://www.nintendo.com/en-gb/Search/Search-299117.html?q=Tri%20Breaker%20A%20Sacred%20Symbols%20Odyssey&f=147394-5-86',
            SearchUrl::forTitle('Tri Breaker: A Sacred Symbols Odyssey', Console::ID_SWITCH_1)
        );
    }

    public function testSeparatingDashesGoButHyphenatedWordsStay()
    {
        $this->assertSame('Twofold Nintendo Switch™ 2 Edition', SearchUrl::searchTerms("Twofold \u{2013} Nintendo Switch™ 2 Edition"));
        $this->assertSame('Dragon Quest I and II HD-2D Remake', SearchUrl::searchTerms('Dragon Quest I and II HD-2D Remake'));
        $this->assertSame('Hades II Nintendo Switch 2 Edition', SearchUrl::searchTerms('Hades II - Nintendo Switch 2 Edition'));
    }
}
