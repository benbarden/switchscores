<?php

namespace App\Domain\JobRun;

/**
 * Lets a command say "I ran, but look at this" when it runs under cron:record (#164).
 *
 * The run is then saved as a warning, which shows amber on the staff home. Outside
 * cron:record - a manual run, or the Tools Hub - warn() does nothing, so a command can
 * call it unconditionally.
 *
 * Static because cron:record runs the command in the same process and needs to read
 * back what it said; start() and finish() bracket each run so nothing carries over.
 */
class CronRunContext
{
    private static bool $active = false;

    private static array $warnings = [];

    public static function start(): void
    {
        self::$active = true;
        self::$warnings = [];
    }

    /**
     * Ends the run and returns its warnings.
     *
     * @return string[]
     */
    public static function finish(): array
    {
        $warnings = self::$warnings;
        self::$active = false;
        self::$warnings = [];

        return $warnings;
    }

    public static function warn(string $message): void
    {
        if (self::$active) {
            self::$warnings[] = $message;
        }
    }
}
