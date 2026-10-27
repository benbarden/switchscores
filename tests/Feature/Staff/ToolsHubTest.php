<?php

namespace Tests\Feature\Staff;

use App\Domain\JobRun\CronRunContext;
use App\Domain\JobRun\JobRecorder;
use App\Jobs\RunArtisanCommand;
use App\Models\JobRun;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;

/**
 * The Tools Hub (#214): tabs from the cron groups in run order plus Ad-hoc, runs recorded under
 * 'tools' through JobRecorder, and a failed step doesn't stop the group. Rows are rolled back.
 */
class ToolsHubTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::command('tools-test:warn', function () {
            CronRunContext::warn('3 things to look at');
        });
        Artisan::command('tools-test:throw', function () {
            throw new \RuntimeException('tool broke');
        });
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Tools Hub Test',
            'email' => 'xx.tools.hub.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function queued(string $command): JobRun
    {
        return JobRun::create([
            'group_key' => JobRecorder::TOOLS_GROUP_KEY,
            'command' => $command,
            'status' => 'queued',
            'queued_at' => now(),
        ]);
    }

    public function testTabsAreTheCronGroupsInRunOrderThenAdHoc()
    {
        $response = $this->actingAs($this->owner())->get('/staff/tools');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Steam reviews', 'Nintendo import', 'Nintendo process', 'Crawl', 'Ad-hoc']);
        $response->assertSee('DSNintendoCoUkApplyPrices --console=1');
        $response->assertSee('game:crawl-batch --limit=10');
    }

    public function testRunQueuesAToolsRow()
    {
        Bus::fake();

        $this->actingAs($this->owner())
            ->post('/staff/tools/run/'.rawurlencode('UpdateGameRanks'))
            ->assertRedirect(route('staff.tools.index', ['tab' => 'games']));

        $run = JobRun::where('command', 'UpdateGameRanks')->latest('id')->first();
        $this->assertSame('tools', $run->group_key);
        $this->assertSame('queued', $run->status);
        Bus::assertDispatched(RunArtisanCommand::class);
    }

    public function testAnUnknownCommandIs404()
    {
        $this->actingAs($this->owner())->post('/staff/tools/run/nope')->assertStatus(404);
    }

    public function testAQueuedRunRecordsItsWarning()
    {
        $run = $this->queued('tools-test:warn');

        (new RunArtisanCommand($run->id))->handle(app(\App\Domain\JobRun\Repository::class), app(JobRecorder::class));

        $run->refresh();
        $this->assertSame('warning', $run->status);
        $this->assertStringStartsWith('Warning: 3 things to look at', $run->output);
    }

    public function testAFailedRunIsRecordedAndDoesNotThrow()
    {
        // Not throwing is what lets "Run group" carry on to the next step
        Exceptions::fake();
        $run = $this->queued('tools-test:throw');

        (new RunArtisanCommand($run->id))->handle(app(\App\Domain\JobRun\Repository::class), app(JobRecorder::class));

        $run->refresh();
        $this->assertSame('failed', $run->status);
        $this->assertStringContainsString('tool broke', $run->output);
        Exceptions::assertReported(\RuntimeException::class);
    }
}
