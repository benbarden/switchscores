<?php

namespace Tests\Feature\Staff;

use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Parsed item detail page (staff.data-sources.detail).
 */
class DataSourceParsedViewTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Parsed View Test',
            'email' => 'xx.parsed.view.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    public function testConsoleShowsAsTheBadge()
    {
        // #204
        $item = DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_2,
            'link_id' => random_int(2000000000, 2100000000),
            'title' => 'Zqx Parsed View Test',
            'release_date_eu' => '2026-10-28',
        ]);

        $response = $this->actingAs($this->owner())->get(route('staff.data-sources.detail', [
            'sourceId' => DataSource::DSID_NINTENDO_CO_UK,
            'linkId' => $item->link_id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('background-color: #c9302c; color: #fff;">Switch 2</label>', false);
    }
}
