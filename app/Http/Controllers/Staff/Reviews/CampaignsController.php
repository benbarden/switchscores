<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\CampaignGame\Repository as CampaignGameRepository;

class CampaignsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CampaignRepository $repoCampaign,
        private CampaignGameRepository $repoCampaignGame
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Review campaigns';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        $bindings['CampaignsList'] = $this->repoCampaign->getAll();

        return view('staff.reviews.campaigns.index', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add campaign';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsCampaignsSubpage($pageTitle))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $name = $request->name;
            $desc = $request->description;
            $progress = $request->progress;
            $isActive = $request->is_active == 'on' ? 1 : 0;

            if (!$progress) {
                $progress = 0;
            }

            $campaign = $this->repoCampaign->create($name, $desc, $progress, $isActive);

            return redirect(route('staff.reviews.campaigns'));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.reviews.campaigns.add', $bindings);
    }

    public function edit($campaignId)
    {
        $pageTitle = 'Edit campaign';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsCampaignsSubpage($pageTitle))->bindings;

        $request = request();

        $campaignData = $this->repoCampaign->find($campaignId);
        if (!$campaignData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $name = $request->name;
            $desc = $request->description;
            $progress = $request->progress;
            $isActive = $request->is_active == 'on' ? 1 : 0;

            $this->repoCampaign->edit($campaignData, $name, $desc, $progress, $isActive);

            return redirect(route('staff.reviews.campaigns'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CampaignData'] = $campaignData;
        $bindings['CampaignId'] = $campaignId;

        return view('staff.reviews.campaigns.edit', $bindings);
    }

    public function editGames($campaignId)
    {
        $pageTitle = 'Edit campaign games';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsCampaignsSubpage($pageTitle))->bindings;

        $request = request();

        $campaignData = $this->repoCampaign->find($campaignId);
        if (!$campaignData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';


            $gameIds = $request->game_ids;
            $gameIdList = explode("\r\n", $gameIds);

            $this->repoCampaignGame->deleteAllByCampaign($campaignId);
            foreach ($gameIdList as $gameId) {
                $this->repoCampaignGame->create($campaignId, $gameId);
            }

            return redirect(route('staff.reviews.campaigns'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CampaignData'] = $campaignData;
        $bindings['CampaignId'] = $campaignId;

        $campaignGameList = $this->repoCampaignGame->byCampaignNumeric($campaignId);
        if ($campaignGameList) {
            $gameIdList = '';
            foreach ($campaignGameList as $listItem) {
                $gameId = $listItem->game_id;
                if ($gameIdList) {
                    $gameIdList .= "\n";
                }
                $gameIdList .= $gameId;
            }
            $bindings['GameIds'] = $gameIdList;
        }

        return view('staff.reviews.campaigns.editGames', $bindings);
    }
}
