<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

use App\Models\JobRun;
use App\Domain\JobRun\Repository as JobRunRepository;

class RunArtisanCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $jobRunId
    ){

    }

    public function handle(JobRunRepository $repoJobRun): void
    {
        // store so failed can use it too
        $this->repoJobRun = $repoJobRun;

        @set_time_limit(0);
        $jobRun = $this->repoJobRun->find($this->jobRunId);
        if (!$jobRun) {
            // Optional: mark as failed in queue for visibility
            $this->fail(new \RuntimeException("JobRun {$this->jobRunId} not found"));
            return;
        }

        $this->repoJobRun->markAsRunning($jobRun);

        $started = hrtime(true);

        try {
            $exitCode = Artisan::call($jobRun->command);
            $output   = Artisan::output(); // stdout only
        } catch (\Throwable $e) {
            // ensure a consistent failed write if Artisan::call bombs
            $this->repoJobRun->markAsFailed($jobRun, $e->getMessage());
            throw $e; // triggers failed() as well (keeps queue state accurate)
        } finally {
            $ended = hrtime(true);
        }

        $isSuccessful = ($exitCode === 0);
        $this->repoJobRun->markAsComplete($jobRun, $isSuccessful, $started, $ended, $exitCode, $output);

        if (!$isSuccessful) {
            // Optional: also fail the queue job so it shows up in failed-jobs tooling
            $this->fail(new \RuntimeException("{$jobRun->command} exited {$exitCode}"));
        }
    }

    public function failed(\Throwable $e): void
    {
        // fallback
        if (!isset($this->repoJobRun)) {
            $this->repoJobRun = app(JobRunRepository::class);
        }

        $jobRun = $this->repoJobRun->find($this->jobRunId);
        if ($jobRun) {
            $this->repoJobRun->markAsFailed($jobRun, $e->getMessage());
        }
    }
}
