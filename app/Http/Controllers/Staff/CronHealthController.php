<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

use App\Console\Commands\CronRecord;
use App\Domain\JobRun\CronHealth;
use App\Domain\JobRun\Repository as JobRunRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

/**
 * One row per nightly job: when it was due, its latest run and what it said (#164).
 * The staff home's Nightly jobs tile links here; each job links to its own runs page.
 */
class CronHealthController extends Controller
{
    const RUN_LIMIT = 30;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CronHealth $cronHealth,
        private JobRunRepository $repoJobRun,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Cron health';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::cronHealth())->bindings;

        $jobs = $this->cronHealth->jobs();

        // In the order they run: by time of day (all jobs are daily), then file order.
        // Problems are already summed up on the staff home tile and in the badges above.
        $order = array_keys($jobs);
        usort($order, fn ($a, $b) => [$jobs[$a]['lastDue']->format('H:i'), $a] <=> [$jobs[$b]['lastDue']->format('H:i'), $b]);

        $bindings['CronJobs'] = array_map(fn ($i) => $jobs[$i], $order);
        $bindings['CronSummary'] = $this->cronHealth->summary($jobs);
        $bindings['GraceMinutes'] = CronHealth::GRACE_MINUTES;

        return view('staff.cron-health', $bindings);
    }

    /**
     * One job's recent runs, with each run's warnings and full output.
     * The command comes as a query parameter: it can hold spaces and colons.
     */
    public function job(Request $request)
    {
        $command = trim((string) $request->query('command', ''));
        if ($command === '') abort(404);

        $job = collect($this->cronHealth->jobs())->firstWhere('command', $command);
        $runs = $this->repoJobRun->getRecentByGroupAndCommand(CronHealth::GROUP_KEY, $command, self::RUN_LIMIT);
        if (!$job && $runs->isEmpty()) abort(404);

        $pageTitle = $command;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::cronHealthJob($pageTitle))->bindings;

        $bindings['Command'] = $command;
        $bindings['Job'] = $job;
        $bindings['Runs'] = $runs->map(fn ($run) => [
            'run' => $run,
            'warnings' => CronHealth::warningsFromOutput($run->output),
            'output' => $this->withoutWarnings($run->output),
        ])->all();
        $bindings['RunLimit'] = self::RUN_LIMIT;
        $bindings['RetentionDays'] = CronRecord::RETENTION_DAYS;

        return view('staff.cron-health-job', $bindings);
    }

    /**
     * The warnings are shown above the output, so drop the lines cron:record put at its top.
     */
    private function withoutWarnings(?string $output): string
    {
        $warnings = CronHealth::warningsFromOutput($output);
        if (!$warnings) {
            return (string) $output;
        }

        $lines = explode("\n", (string) $output);
        return ltrim(implode("\n", array_slice($lines, count($warnings))), "\n");
    }
}
