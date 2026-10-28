<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Domain\JobRun\JobRecorder;
use App\Domain\JobRun\Repository as JobRunRepository;

/**
 * Runs one Tools Hub command on the queue. The job_runs row was created as queued on
 * the click; JobRecorder runs it and records the result, warnings included, and reports any
 * exception to Sentry.
 *
 * handle() doesn't throw for a failed command - JobRecorder has already recorded and reported
 * it - so "Run group" (a job chain) carries on to the next step, like cron:group.
 */
class RunArtisanCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $jobRunId
    ){

    }

    public function handle(JobRunRepository $repoJobRun, JobRecorder $jobRecorder): void
    {
        @set_time_limit(0);

        $jobRun = $repoJobRun->find($this->jobRunId);
        if (!$jobRun) {
            $this->fail(new \RuntimeException("JobRun {$this->jobRunId} not found"));
            return;
        }

        $jobRecorder->runExisting($jobRun);
    }

    /**
     * The worker died or timed out mid-run: JobRecorder never got to record the result.
     */
    public function failed(\Throwable $e): void
    {
        $repoJobRun = app(JobRunRepository::class);

        $jobRun = $repoJobRun->find($this->jobRunId);
        if ($jobRun && in_array($jobRun->status, ['queued', 'running'], true)) {
            $repoJobRun->markAsFailed($jobRun, $e->getMessage());
        }
    }
}
