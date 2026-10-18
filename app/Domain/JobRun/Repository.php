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

    /**
     * A cron run starts straight away, so it skips the queued state (#164).
     */
    public function createRunningJob($groupKey, $command)
    {
        return JobRun::create([
            'group_key' => $groupKey,
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function getLastByCommand($command)
    {
        return JobRun::where('command', $command)->latest('id')->first();
    }

    public function getLastByGroupAndCommand($groupKey, $command)
    {
        return JobRun::where('group_key', $groupKey)->where('command', $command)->latest('id')->first();
    }

    public function getRecentByGroupAndCommand($groupKey, $command, int $limit)
    {
        return JobRun::where('group_key', $groupKey)->where('command', $command)->latest('id')->limit($limit)->get();
    }

    public function deleteByGroupStartedBefore($groupKey, \DateTimeInterface $before): int
    {
        return JobRun::where('group_key', $groupKey)->where('started_at', '<', $before)->delete();
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
        $jobRun->output = self::limitOutput($output);
        $jobRun->save();
    }

    // job_runs.output is TEXT: 65,535 bytes. Leaves room for the cut marker.
    const OUTPUT_LIMIT_BYTES = 65000;
    const OUTPUT_HEAD_BYTES = 8000;

    /**
     * Stores output whole when it fits the column, which is nearly always (#164).
     *
     * The old limit was 16,000 characters, which cut ApplyPrices' ~28KB report (Ben,
     * 2026-10-18: use the column's full size rather than change it). Only output over
     * ~64KB - e.g. a night when most Switch 2 prices disagree - is cut, and then from the
     * middle: cron:record's warnings are at the top and summaries come last.
     * Measured in bytes, cut on character boundaries (mb_strcut), so UTF-8 stays valid.
     */
    public static function limitOutput(?string $output): string
    {
        $output = $output ?? '';
        $bytes = strlen($output);
        if ($bytes <= self::OUTPUT_LIMIT_BYTES) {
            return $output;
        }

        $head = mb_strcut($output, 0, self::OUTPUT_HEAD_BYTES);
        $tailBytes = self::OUTPUT_LIMIT_BYTES - self::OUTPUT_HEAD_BYTES;
        $tail = mb_strcut($output, $bytes - $tailBytes);
        $cut = $bytes - strlen($head) - strlen($tail);

        return $head
            ."\n\n[… ".number_format($cut)." bytes cut from the middle …]\n\n"
            .$tail;
    }

    /**
     * Ran and exited 0, but the command raised warnings via CronRunContext (#164).
     */
    public function markAsWarning(JobRun $jobRun): void
    {
        $jobRun->status = 'warning';
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