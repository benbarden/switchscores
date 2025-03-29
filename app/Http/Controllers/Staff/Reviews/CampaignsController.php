<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\CampaignGame\Repository as CampaignGameRepository;

class CampaignsController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
    ];

    public function __construct(
        private CampaignRepository $repoCampaign,
        private CampaignGameRepository $repoCampaignGame
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Review campaigns';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['CampaignsList'] = $this->repoCampaign->getAll();

        return view('staff.reviews.campaigns.index', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add campaign';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsCampaignsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsCampaignsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsCampaignsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
