<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

use App\Models\JobRun;
use App\Jobs\RunArtisanCommand;
use App\Domain\JobRun\Repository as JobRunRepository;

class ToolsController extends Controller
{
    public function __construct(
        private JobRunRepository $repoJobRun
    )
    {

    }

    public function index(Request $req)
    {
        $pageTitle = 'Tools hub';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $groups = config('tools.groups');
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
        [$groupKey, $jobMeta] = $this->findJobOrAbort($cmd);

        $run = $this->repoJobRun->createQueuedJob($groupKey, $cmd);

        RunArtisanCommand::dispatch($run->id);

        return redirect()->route('staff.tools.index', ['tab' => $groupKey])
            ->with('status', "Queued: {$cmd}");
    }

    public function runGroup(Request $req, string $groupKey)
    {
        $group = config("tools.groups.$groupKey");
        abort_unless($group, 404);

        $chain = [];
        foreach ($group['jobs'] as $job) {
            $run = $this->repoJobRun->createQueuedJob($groupKey, $job['cmd']);
            $chain[] = new RunArtisanCommand($run->id);
        }

        Bus::chain($chain)->dispatch();

        return redirect()->route('staff.tools.index', ['tab'=>$groupKey])
            ->with('status', "Queued group: {$group['label']}");
    }

    private function findJobOrAbort(string $cmd): array
    {
        foreach (config('tools.groups') as $gKey => $group) {
            foreach ($group['jobs'] as $job) {
                if ($job['cmd'] === $cmd) return [$gKey, $job];
            }
        }
        abort(404);
    }
}