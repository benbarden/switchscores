<?php

namespace Tests\Unit\Domain\JobRun;

use App\Domain\JobRun\CronHealth;
use App\Domain\JobRun\CronSchedule;
use App\Domain\JobRun\Repository as JobRunRepository;
use App\Models\JobRun;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Cover for reading the cron file and deciding each job's state (#164).
 */
class CronHealthTest extends TestCase
{
    private function health(): CronHealth
    {
        return new CronHealth(new CronSchedule('/nonexistent'), new JobRunRepository());
    }

    private function makeRun(string $status, string $startedAt): JobRun
    {
        $run = new JobRun();
        $run->status = $status;
        $run->started_at = Carbon::parse($startedAt);
        return $run;
    }

    public function testParsesAWrappedLine()
    {
        $job = (new CronSchedule('/nonexistent'))->parseLine(
            '15 4 * * * www-data cd /var/www/switchscores.com && php artisan cron:record "DSNintendoCoUkUpdateGames" >/dev/null 2>&1'
        );

        $this->assertSame(['expression' => '15 4 * * *', 'command' => 'DSNintendoCoUkUpdateGames'], $job);
    }

    public function testParsesAWrappedLineWithArguments()
    {
        $job = (new CronSchedule('/nonexistent'))->parseLine(
            '30 6 * * * www-data cd /var/www/switchscores.com && php artisan cron:record "game:crawl-batch --limit=100 --delay=3" >/dev/null 2>&1'
        );

        $this->assertSame('game:crawl-batch --limit=100 --delay=3', $job['command']);
    }

    public function testParsesABareLine()
    {
        $job = (new CronSchedule('/nonexistent'))->parseLine(
            '0 3 * * * www-data cd /var/www/switchscores.com && php artisan game:sync-steam-reviews >/dev/null 2>&1'
        );

        $this->assertSame(['expression' => '0 3 * * *', 'command' => 'game:sync-steam-reviews'], $job);
    }

    public function testSkipsCommentsAndBlankLines()
    {
        $schedule = new CronSchedule('/nonexistent');

        $this->assertNull($schedule->parseLine('# Data ingestion'));
        $this->assertNull($schedule->parseLine(''));
    }

    public function testAGroupLineBecomesOneJobPerStep()
    {
        // #185: cron:group runs each step on its own row, so each step is an expected job
        config(['cron-groups.test-games' => ['label' => 'Test', 'steps' => [
            'UpdateGameReviewStats' => 'Review stats', 'UpdateGameRanks' => 'Ranks',
        ]]]);

        $jobs = (new CronSchedule('/nonexistent'))->parseLineJobs(
            '30 5 * * * www-data cd /var/www/switchscores.com && php artisan cron:group test-games >/dev/null 2>&1'
        );

        $this->assertSame([
            ['expression' => '30 5 * * *', 'command' => 'UpdateGameReviewStats'],
            ['expression' => '30 5 * * *', 'command' => 'UpdateGameRanks'],
        ], $jobs);
    }

    public function testAnUnknownGroupHasNoJobs()
    {
        $jobs = (new CronSchedule('/nonexistent'))->parseLineJobs(
            '30 5 * * * www-data cd /var/www/switchscores.com && php artisan cron:group no-such-group >/dev/null 2>&1'
        );

        $this->assertSame([], $jobs);
    }

    public function testTheRepoCronFileParsesEveryJobLine()
    {
        $path = base_path('scripts/switchscores-cron');
        $schedule = new CronSchedule($path);
        $jobLines = array_filter(file($path, FILE_IGNORE_NEW_LINES), fn ($l) => trim($l) !== '' && !str_starts_with(trim($l), '#'));

        // Every line is a known group or a single job, and the cron file uses every group
        $expected = 0;
        foreach ($jobLines as $line) {
            $jobs = $schedule->parseLineJobs($line);
            $this->assertNotEmpty($jobs, 'Line has no jobs: '.$line);
            $expected += count($jobs);
        }
        $this->assertCount($expected, $schedule->jobs());
        $this->assertCount(array_sum(array_map(fn ($g) => count($g['steps']), config('cron-groups'))), $schedule->jobs());
    }

    public function testGroupOrderFollowsTheCronFileThenUnscheduledGroups()
    {
        // #214: the Tools Hub's tabs, in the order the groups run
        config(['cron-groups.test-unscheduled' => ['label' => 'Test', 'steps' => ['UpdateGameRanks' => 'Ranks']]]);

        $order = (new CronSchedule(base_path('scripts/switchscores-cron')))->groupOrder();

        $this->assertSame('steam', $order[0]);
        $this->assertSame('crawl', $order[count($order) - 2]);
        $this->assertSame('test-unscheduled', end($order));
    }

    public function testARunSinceItWasDueKeepsItsStatus()
    {
        $now = Carbon::parse('2026-10-19 09:00:00');
        $lastDue = Carbon::parse('2026-10-19 04:15:00');

        $this->assertSame('success', $this->health()->state($this->makeRun('success', '2026-10-19 04:15:01'), $lastDue, $now));
        $this->assertSame('warning', $this->health()->state($this->makeRun('warning', '2026-10-19 04:15:01'), $lastDue, $now));
        $this->assertSame('failed', $this->health()->state($this->makeRun('failed', '2026-10-19 04:15:01'), $lastDue, $now));
    }

    public function testNoRunWithinTheGracePeriodIsPending()
    {
        $now = Carbon::parse('2026-10-19 04:40:00');
        $lastDue = Carbon::parse('2026-10-19 04:15:00');

        $this->assertSame('pending', $this->health()->state($this->makeRun('success', '2026-10-18 04:15:01'), $lastDue, $now));
        $this->assertSame('pending', $this->health()->state(null, $lastDue, $now));
    }

    public function testNoRunAfterTheGracePeriodIsMissed()
    {
        $now = Carbon::parse('2026-10-19 04:46:00');
        $lastDue = Carbon::parse('2026-10-19 04:15:00');

        // Yesterday's run doesn't count for today
        $this->assertSame('missed', $this->health()->state($this->makeRun('success', '2026-10-18 04:15:01'), $lastDue, $now));
        $this->assertSame('missed', $this->health()->state(null, $lastDue, $now));
    }

    public function testARunStillRunningAfterTwoHoursIsStuck()
    {
        $lastDue = Carbon::parse('2026-10-19 04:15:00');
        $run = $this->makeRun('running', '2026-10-19 04:15:01');

        $this->assertSame('running', $this->health()->state($run, $lastDue, Carbon::parse('2026-10-19 05:00:00')));
        $this->assertSame('stuck', $this->health()->state($run, $lastDue, Carbon::parse('2026-10-19 06:30:00')));
    }

    public function testOutputThatFitsTheColumnIsKeptWhole()
    {
        // About the size of ApplyPrices' report on localdev, 2026-10-18 (~28KB)
        $output = "Warning: 175 prices differ\n".str_repeat("| changed | 16551 | Atelier Ryza 2 | 32.99 |\n", 600)."--- Outcomes ---";

        $this->assertSame($output, JobRunRepository::limitOutput($output));
        $this->assertSame("Done.\n", JobRunRepository::limitOutput("Done.\n"));
        $this->assertSame('', JobRunRepository::limitOutput(null));
    }

    public function testOutputTooBigForTheColumnKeepsItsStartAndEnd()
    {
        $output = "Warning: 500 prices differ\n".str_repeat("| changed | Pokémon … row |\n", 5000)."--- Outcomes ---\nchanged: 500";

        $limited = JobRunRepository::limitOutput($output);

        $this->assertLessThanOrEqual(65535, strlen($limited), 'Must fit a TEXT column');
        $this->assertTrue(mb_check_encoding($limited, 'UTF-8'), 'Must not split a character');
        $this->assertStringStartsWith('Warning: 500 prices differ', $limited);
        $this->assertStringEndsWith("--- Outcomes ---\nchanged: 500", $limited);
        $this->assertStringContainsString('bytes cut from the middle', $limited);
    }

    public function testWarningsRoundTripThroughTheOutput()
    {
        $output = CronHealth::formatOutput(['3 prices differ', 'Relay slow'], "Table...\nDone.");

        $this->assertSame(['3 prices differ', 'Relay slow'], CronHealth::warningsFromOutput($output));
        $this->assertSame([], CronHealth::warningsFromOutput("Table...\nWarning: not at the top"));
    }
}
