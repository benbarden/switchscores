<?php

namespace App\Http\Controllers\Admin;

use App\Factories\GamesCompanyFactory;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use Auth;

use App\Partner;

class GamesCompanyController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games companies';
        $bindings['PageTitle'] = 'Games companies';

        $bindings['GamesCompanyList'] = $servicePartner->getAllGamesCompanies();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.partners.games-company.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $servicePartner = $serviceContainer->getPartnerService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $status = Partner::STATUS_ACTIVE;

            $partner = GamesCompanyFactory::create(
                $status, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );
            $partner->save();

            return redirect(route('admin.partners.games-company.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Add games company';
        $bindings['PageTitle'] = 'Add games company';
        $bindings['FormMode'] = 'add';

        return view('admin.partners.games-company.add', $bindings);
    }

    public function edit($partnerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $servicePartner = $serviceContainer->getPartnerService();

        $partnerData = $servicePartner->find($partnerId);
        if (!$partnerData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $servicePartner->editGamesCompany(
                $partnerData, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.partners.games-company.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit games company';
        $bindings['PageTitle'] = 'Edit games company';
        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['StatusList'] = $statusList;

        return view('admin.partners.games-company.edit', $bindings);
    }

    public function delete($partnerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $partnerData = $servicePartner->find($partnerId);
        if (!$partnerData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameDevelopers = $serviceGameDeveloper->getByDeveloperId($partnerId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Games company is marked as the developer for '.count($gameDevelopers).' game(s)';
        }
        $gamePublishers = $serviceGamePublisher->getByPublisherId($partnerId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Games company is marked as the publisher for '.count($gamePublishers).' game(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $servicePartner->deleteGamesCompany($partnerId);

            return redirect(route('admin.partners.games-company.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Delete games company';
        $bindings['PageTitle'] = 'Delete games company';
        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.partners.games-company.delete', $bindings);
    }

}
