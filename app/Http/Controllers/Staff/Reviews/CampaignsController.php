<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Domain\Campaign\Repository as CampaignRepository;

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

    protected $repoCampaign;

    public function __construct(
        CampaignRepository $repoCampaign
    )
    {
        $this->repoCampaign = $repoCampaign;
    }

    public function show()
    {
        $bindings = $this->getBindingsReviewsSubpage('Review campaigns');

        $bindings['CampaignsList'] = $this->repoCampaign->getAll();

        return view('staff.reviews.campaigns.index', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Add campaign');

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
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Edit campaign');

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
        $bindings = $this->getBindingsReviewsCampaignsSubpage('Edit campaign games');

        $request = request();

        $serviceCampaignGame = $this->getServiceCampaignGame();

        $campaignData = $this->repoCampaign->find($campaignId);
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
