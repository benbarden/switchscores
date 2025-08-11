<?php

namespace App\Domain\JobRun;

use App\Models\JobRun;
use Illuminate\Support\Str;

class Repository
{
    public function createQueuedJob($groupKey, $command)
    {
        return JobRun::create([
            'group_key' => $groupKey,
            'command' => $command,
            'status' => 'queued',
            'queued_at' => now(),
        ]);
    }

    public function getLastByCommand($command)
    {
        return JobRun::where('command', $command)->latest('id')->first();
    }

    public function find($id)
    {
        return JobRun::find($id);
    }

    public function markAsRunning(JobRun $jobRun)
    {
        $jobRun->status = 'running';
        $jobRun->started_at = now();
        $jobRun->save();
    }

    public function markAsComplete(JobRun $jobRun, bool $isSuccessful, int $started, int $ended, int $exitCode, ?string $output): void
    {
        $jobRun->status = $isSuccessful ? 'success' : 'failed';
        $jobRun->exit_code = $exitCode;
        $jobRun->finished_at = now();
        $jobRun->duration_ms = (int) (($ended - $started)/1_000_000);
        $jobRun->output = Str::limit($output ?? '', 16000, '…');
        $jobRun->save();
    }

    public function markAsFailed(JobRun $jobRun, ?string $errorMessage): void
    {
        $jobRun->status = 'failed';
        $jobRun->finished_at = now();
        $jobRun->output      = Str::limit($errorMessage ?? '', 16000, '…');
        $jobRun->save();
    }
}