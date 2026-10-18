<?php

namespace Tests\Feature\Console;

use App\Domain\JobRun\CronRunContext;
use App\Models\JobRun;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;

/**
 * Cover for the cron:record wrapper (#164). Rows are rolled back after each test.
 */
class CronRecordTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::command('cron-test:ok {--name=}', function () {
            $this->info('hello '.$this->option('name'));
        });
        Artisan::command('cron-test:warn', function () {
            CronRunContext::warn('3 prices differ');
            $this->info('report');
        });
        Artisan::command('cron-test:exit-one', function () {
            return 1;
        });
        Artisan::command('cron-test:throw', function () {
            throw new \RuntimeException('relay down');
        });
    }

    private function lastRun(string $command): ?JobRun
    {
        return JobRun::where('group_key', 'cron')->where('command', $command)->latest('id')->first();
    }

    public function testRecordsASuccessfulRunWithItsOutput()
    {
        $exitCode = Artisan::call('cron:record', ['cmd' => 'cron-test:ok --name=world']);

        $run = $this->lastRun('cron-test:ok --name=world');
        $this->assertSame(0, $exitCode);
        $this->assertSame('success', $run->status);
        $this->assertSame(0, $run->exit_code);
        $this->assertNotNull($run->started_at);
        $this->assertNotNull($run->finished_at);
        $this->assertStringContainsString('hello world', $run->output);
    }

    public function testAWarningMakesItAWarningRun()
    {
        Artisan::call('cron:record', ['cmd' => 'cron-test:warn']);

        $run = $this->lastRun('cron-test:warn');
        $this->assertSame('warning', $run->status);
        $this->assertStringStartsWith('Warning: 3 prices differ', $run->output);
    }

    public function testWarningsOutsideTheWrapperDoNothing()
    {
        Artisan::call('cron-test:warn');

        $this->assertSame([], CronRunContext::finish());
    }

    public function testANonZeroExitIsAFailedRun()
    {
        $exitCode = Artisan::call('cron:record', ['cmd' => 'cron-test:exit-one']);

        $run = $this->lastRun('cron-test:exit-one');
        $this->assertSame(1, $exitCode);
        $this->assertSame('failed', $run->status);
        $this->assertSame(1, $run->exit_code);
    }

    public function testAnExceptionIsRecordedAndStillReported()
    {
        Exceptions::fake();

        $exitCode = Artisan::call('cron:record', ['cmd' => 'cron-test:throw']);

        $run = $this->lastRun('cron-test:throw');
        $this->assertSame(1, $exitCode);
        $this->assertSame('failed', $run->status);
        $this->assertStringContainsString('relay down', $run->output);
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function testCronRunsOlderThanSixtyDaysArePruned()
    {
        $old = JobRun::create(['group_key' => 'cron', 'command' => 'cron-test:ok', 'status' => 'success', 'started_at' => now()->subDays(61)]);
        $recent = JobRun::create(['group_key' => 'cron', 'command' => 'cron-test:ok', 'status' => 'success', 'started_at' => now()->subDays(59)]);
        $toolsHub = JobRun::create(['group_key' => 'reviews', 'command' => 'cron-test:ok', 'status' => 'success', 'started_at' => now()->subDays(90)]);

        Artisan::call('cron:record', ['cmd' => 'cron-test:ok']);

        $this->assertNull(JobRun::find($old->id));
        $this->assertNotNull(JobRun::find($recent->id));
        $this->assertNotNull(JobRun::find($toolsHub->id), 'Tools Hub runs are not pruned');
    }
}
