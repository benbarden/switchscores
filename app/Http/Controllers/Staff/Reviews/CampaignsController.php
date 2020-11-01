<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Campaign;

use App\Traits\SwitchServices;

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

    private function getListBindings($pageTitle, $tableSort = '')
    {
        $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs();

        if ($pageTitle == 'Review campaigns') {
            $breadcrumbs = $breadcrumbs->makeReviewsSubPage($pageTitle);
        } else {
            $breadcrumbs = $breadcrumbs->makeReviewsCampaignsSubPage($pageTitle);
        }

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Reviews')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        }

        return $bindings->getBindings();
    }

    public function show()
    {
        $bindings = $this->getListBindings('Review campaigns', "[ 0, 'desc']");

        $bindings['CampaignsList'] = $this->getServiceCampaign()->getAll();

        return view('staff.reviews.campaigns.index', $bindings);
    }

    public function add()
    {
        $bindings = $this->getListBindings('Add campaign');

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
        $bindings = $this->getListBindings('Edit campaign');

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
}
