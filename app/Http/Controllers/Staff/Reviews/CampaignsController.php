<?php

namespace App\Http\Controllers\Staff\Reviews;

use App\Traits\StaffView;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class CampaignsController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
    ];

    public function show()
    {
        $bindings = $this->getBindingsReviewsSubpage('Review campaigns');

        $bindings['CampaignsList'] = $this->getServiceCampaign()->getAll();

        return view('staff.reviews.campaigns.index', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Add campaign');

        $request = request();

        $serviceCampaign = $this->getServiceCampaign();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $name = $request->name;
            $desc = $request->description;
            $progress = $request->progress;
            $isActive = $request->is_active == 'on' ? 1 : 0;

            if (!$progress) {
                $progress = 0;
            }

            $campaign = $serviceCampaign->create($name, $desc, $progress, $isActive);

            return redirect(route('staff.reviews.campaigns'));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.reviews.campaigns.add', $bindings);
    }

    public function edit($campaignId)
    {
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Edit campaign');

        $request = request();

        $serviceCampaign = $this->getServiceCampaign();

        $campaignData = $serviceCampaign->find($campaignId);
        if (!$campaignData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $name = $request->name;
            $desc = $request->description;
            $progress = $request->progress;
            $isActive = $request->is_active == 'on' ? 1 : 0;

            $serviceCampaign->edit($campaignData, $name, $desc, $progress, $isActive);

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
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Edit campaign games');

        $request = request();

        $serviceCampaign = $this->getServiceCampaign();
        $serviceCampaignGame = $this->getServiceCampaignGame();

        $campaignData = $serviceCampaign->find($campaignId);
        if (!$campaignData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';


            $gameIds = $request->game_ids;
            $gameIdList = explode("\r\n", $gameIds);

            $serviceCampaignGame->deleteAllByCampaign($campaignId);
            foreach ($gameIdList as $gameId) {
                $serviceCampaignGame->create($campaignId, $gameId);
            }

            return redirect(route('staff.reviews.campaigns'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CampaignData'] = $campaignData;
        $bindings['CampaignId'] = $campaignId;

        $campaignGameList = $serviceCampaignGame->getByCampaignNumeric($campaignId);
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
