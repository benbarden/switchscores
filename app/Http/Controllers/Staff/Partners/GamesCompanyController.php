<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

use App\Factories\GamesCompanyFactory;
use App\Partner;

class GamesCompanyController extends Controller
{
    use SwitchServices;

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
        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Games companies';
        $bindings['PageTitle'] = 'Games companies';

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getAllGamesCompanies();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutTwitterIds()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Games companies without Twitter Ids';
        $bindings['PageTitle'] = 'Games companies without Twitter Ids';

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithoutTwitterIds();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Games companies without website URLs';
        $bindings['PageTitle'] = 'Games companies without website URLs';

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithoutWebsiteUrls();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.partners.games-company.list', $bindings);
    }

    public function show(Partner $partner)
    {
        if (!$partner->isGamesCompany()) abort(404);

        $servicePartner = $this->getServicePartner();

        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $partnerId = $partner->id;

        $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($partnerId, false);
        $gamePubList = $serviceGamePublisher->getGamesByPublisher($partnerId, false);

        $mergedGameList = $servicePartner->getMergedGameList($gameDevList, $gamePubList);

        $bindings = [];

        $bindings['TopTitle'] = $partner->name;
        $bindings['PageTitle'] = $partner->name;

        $bindings['PartnerData'] = $partner;
        $bindings['PartnerId'] = $partnerId;

        $bindings['MergedGameList'] = $mergedGameList;

        return view('staff.partners.games-company.show', $bindings);
    }

    public function devsWithUnrankedGames()
    {
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $pageTitle = 'Outreach targets: Developers with unranked games';
        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['GamesCompanyList'] = $servicePartner->getDevelopersWithUnrankedGames();

        $bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function pubsWithUnrankedGames()
    {
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $pageTitle = 'Outreach targets: Publishers with unranked games';
        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['GamesCompanyList'] = $servicePartner->getPublishersWithUnrankedGames();
        $bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function add()
    {
        $servicePartner = $this->getServicePartner();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $partner = GamesCompanyFactory::createActive(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );
            $partner->save();

            return redirect(route('staff.partners.games-company.show', ['partner' => $partner]));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Add games company';
        $bindings['PageTitle'] = 'Add games company';
        $bindings['FormMode'] = 'add';

        return view('staff.partners.games-company.add', $bindings);
    }

    public function edit($partnerId)
    {
        $servicePartner = $this->getServicePartner();

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

            return redirect(route('staff.partners.games-company.show', ['partner' => $partnerData]));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Edit games company';
        $bindings['PageTitle'] = 'Edit games company';
        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['StatusList'] = $statusList;

        return view('staff.partners.games-company.edit', $bindings);
    }

    public function delete($partnerId)
    {
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

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

            return redirect(route('staff.partners.games-company.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Staff - Delete games company';
        $bindings['PageTitle'] = 'Delete games company';
        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.partners.games-company.delete', $bindings);
    }

}
