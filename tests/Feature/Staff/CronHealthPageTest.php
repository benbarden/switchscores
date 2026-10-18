<?php

namespace Tests\Feature\Staff;

use App\Models\JobRun;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The Cron health page and a job's runs page (#164) render.
 */
class CronHealthPageTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Cron Health Test',
            'email' => 'xx.cron.health.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    public function testOverviewListsTheCronFileJobs()
    {
        $response = $this->actingAs($this->owner())->get('/staff/cron-health');

        $response->assertStatus(200);
        $response->assertSee('DSNintendoCoUkUpdateGames');
        $response->assertSee('game:crawl-batch --limit=100 --delay=3');
    }

    public function testOverviewIsInRunOrder()
    {
        $response = $this->actingAs($this->owner())->get('/staff/cron-health');

        // Steam (03:00) is last in the file but first to run; the crawl (06:30) is last
        $response->assertSeeInOrder([
            'game:sync-steam-reviews',
            'DSNintendoCoUkImportParseLink',
            'DSNintendoCoUkApplyPrices',
            'PartnerImportActiveFeeds',
            'game:crawl-batch',
        ]);
    }

    public function testJobPageShowsARunsWarningAndOutput()
    {
        JobRun::create([
            'group_key' => 'cron',
            'command' => 'DSNintendoCoUkApplyPrices',
            'status' => 'warning',
            'exit_code' => 0,
            'started_at' => now(),
            'output' => "Warning: 2 Switch 2 price(s) differ from the price API after UpdateGames: 1, 2\n\n--- Outcomes ---",
        ]);

        $response = $this->actingAs($this->owner())->get('/staff/cron-health/job?command=DSNintendoCoUkApplyPrices');

        $response->assertStatus(200);
        $response->assertSee('2 Switch 2 price(s) differ');
        $response->assertSee('--- Outcomes ---');
    }

    public function testJobPageForAnUnknownCommandIs404()
    {
        $this->actingAs($this->owner())->get('/staff/cron-health/job?command=nope')->assertStatus(404);
    }
}
