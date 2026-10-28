<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Jobs\RunArtisanCommand;
use App\Domain\JobRun\CronSchedule;
use App\Domain\JobRun\JobRecorder;
use App\Domain\JobRun\Repository as JobRunRepository;

/**
 * The Tools Hub: one tab per nightly cron group, in the order they run, from
 * config/cron-groups.php - so the hub and the schedule can't drift apart - then the Ad-hoc
 * commands from config/tools.php.
 *
 * Runs are recorded under group_key 'tools', so they don't count as nightly runs on cron
 * health. "Last run" shows the latest run of the command either way.
 */
class ToolsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private JobRunRepository $repoJobRun,
        private CronSchedule $cronSchedule,
    )
    {

    }

    public function index(Request $req)
    {
        $pageTitle = 'Tools hub';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::toolsHub())->bindings;

        $groups = $this->groups();
        $requested = $req->query('tab');
        $activeTab = array_key_exists($requested, $groups) ? $requested : array_key_first($groups);

        // Pull latest run per command
        $last = [];
        foreach ($groups as $gKey => $g) {
            foreach ($g['jobs'] as $job) {
                $command = $job['cmd'];
                $last[$command] = $this->repoJobRun->getLastByCommand($command);
            }
        }

        $bindings['groups'] = $groups;
        $bindings['activeTab'] = $activeTab;
        $bindings['last'] = $last;

        return view('staff.tools', $bindings);
    }

    public function run(Request $req, string $cmd)
    {
        $groupKey = $this->findGroupOrAbort($cmd);

        $run = $this->repoJobRun->createQueuedJob(JobRecorder::TOOLS_GROUP_KEY, $cmd);

        RunArtisanCommand::dispatch($run->id);

        return redirect()->route('staff.tools.index', ['tab' => $groupKey])
            ->with('status', "Queued: {$cmd}");
    }

    public function runGroup(Request $req, string $groupKey)
    {
        $group = $this->groups()[$groupKey] ?? null;
        abort_unless($group, 404);

        // Every row is created now, so the whole group shows as queued straight away
        $chain = [];
        foreach ($group['jobs'] as $job) {
            $run = $this->repoJobRun->createQueuedJob(JobRecorder::TOOLS_GROUP_KEY, $job['cmd']);
            $chain[] = new RunArtisanCommand($run->id);
        }

        Bus::chain($chain)->dispatch();

        return redirect()->route('staff.tools.index', ['tab'=>$groupKey])
            ->with('status', "Queued group: {$group['label']}");
    }

    /**
     * Tab key => ['label' => ..., 'jobs' => [['cmd' => ..., 'desc' => ...], ...]]
     */
    private function groups(): array
    {
        $groups = [];

        foreach ($this->cronSchedule->groupOrder() as $name) {
            $groups[$name] = $this->tab(config('cron-groups.'.$name));
        }
        foreach (config('tools') as $name => $group) {
            $groups[$name] = $this->tab($group);
        }

        return $groups;
    }

    private function tab(array $group): array
    {
        $jobs = [];
        foreach ($group['steps'] as $cmd => $desc) {
            $jobs[] = ['cmd' => $cmd, 'desc' => $desc];
        }

        return ['label' => $group['label'], 'jobs' => $jobs];
    }

    private function findGroupOrAbort(string $cmd): string
    {
        foreach ($this->groups() as $gKey => $group) {
            foreach ($group['jobs'] as $job) {
                if ($job['cmd'] === $cmd) return $gKey;
            }
        }
        abort(404);
    }
}
