<?php

namespace Tests\Feature\Console;

use App\Domain\JobRun\CronRunContext;
use App\Models\JobRun;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;

/**
 * Cover for cron:group (#185): each step is recorded in job_runs on its own, as cron:record
 * would record it, and a failed step doesn't stop the rest. Rows are rolled back after each test.
 */
class CronGroupTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::command('cron-group-test:one', function () {
            $this->info('one ran');
        });
        Artisan::command('cron-group-test:warn', function () {
            CronRunContext::warn('2 things to look at');
        });
        Artisan::command('cron-group-test:throw', function () {
            throw new \RuntimeException('step broke');
        });
        Artisan::command('cron-group-test:three', function () {
            $this->info('three ran');
        });
    }

    private function lastRun(string $command): ?JobRun
    {
        return JobRun::where('group_key', 'cron')->where('command', $command)->latest('id')->first();
    }

    public function testRunsEveryStepAndRecordsEachOne()
    {
        config(['cron-groups.test-ok' => ['label' => 'Test', 'steps' => [
            'cron-group-test:one' => 'One', 'cron-group-test:warn' => 'Warn', 'cron-group-test:three' => 'Three',
        ]]]);

        $exitCode = Artisan::call('cron:group', ['name' => 'test-ok']);

        $this->assertSame(0, $exitCode);
        $this->assertSame('success', $this->lastRun('cron-group-test:one')->status);
        $this->assertStringContainsString('one ran', $this->lastRun('cron-group-test:one')->output);
        $this->assertSame('warning', $this->lastRun('cron-group-test:warn')->status);
        $this->assertSame('success', $this->lastRun('cron-group-test:three')->status);
        // The group itself isn't a job: only its steps are recorded
        $this->assertNull($this->lastRun('cron:group test-ok'));
    }

    public function testAFailedStepIsRecordedAndTheRestStillRun()
    {
        Exceptions::fake();
        config(['cron-groups.test-fail' => ['label' => 'Test', 'steps' => [
            'cron-group-test:one' => 'One', 'cron-group-test:throw' => 'Throw', 'cron-group-test:three' => 'Three',
        ]]]);

        $exitCode = Artisan::call('cron:group', ['name' => 'test-fail']);

        $this->assertSame(1, $exitCode);
        $this->assertSame('failed', $this->lastRun('cron-group-test:throw')->status);
        $this->assertStringContainsString('step broke', $this->lastRun('cron-group-test:throw')->output);
        $this->assertSame('success', $this->lastRun('cron-group-test:three')->status);
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function testEveryConfiguredStepIsARealCommand()
    {
        // A typo or a rename (GameUpdateQualityScores, #185) should fail here, not at 5:30am
        // The Tools Hub's ad-hoc commands (#214) too, and every group has a label and descriptions
        $commands = array_keys(Artisan::all());
        foreach (array_merge(config('cron-groups'), config('tools')) as $group => $config) {
            $this->assertNotEmpty($config['label'] ?? null, "Group $group: no label");
            foreach ($config['steps'] as $step => $desc) {
                $name = strtok($step, ' ');
                $this->assertContains($name, $commands, "Group $group: no command $name");
                $this->assertNotEmpty($desc, "Group $group: no description for $step");
            }
        }
    }

    public function testAnUnknownGroupFails()
    {
        $this->assertSame(1, Artisan::call('cron:group', ['name' => 'no-such-group']));
    }
}
