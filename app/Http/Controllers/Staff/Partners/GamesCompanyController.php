<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\Partner;
use App\Factories\GamesCompanyFactory;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\StaffView;
use App\Traits\SwitchServices;

class GamesCompanyController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $gameQualityFilter;
    private $repoGamesCompany;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        GameQualityFilter $gameQualityFilter,
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->gameQualityFilter = $gameQualityFilter;
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function showList()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getAll();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function normalQuality()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->normalQuality();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function lowQuality()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->lowQuality();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutTwitterIds()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies without Twitter Ids', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithoutTwitterIds();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies without website URLs', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithoutWebsiteUrls();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function duplicateTwitterIds()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies with duplicate Twitter Ids', "[ 0, 'desc']");

        $duplicateTwitterIdsList = $this->getServicePartner()->getGamesCompanyDuplicateTwitterIds();
        if ($duplicateTwitterIdsList) {
            $idList = [];
            foreach ($duplicateTwitterIdsList as $duplicateTwitterId) {
                $idList[] = $duplicateTwitterId->twitter_id;
            }
            $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithTwitterIdList($idList);
        }

        return view('staff.partners.games-company.list', $bindings);
    }

    public function duplicateWebsiteUrls()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies with duplicate website URLs', "[ 0, 'desc']");

        $duplicateWebsiteUrlsList = $this->getServicePartner()->getGamesCompanyDuplicateWebsiteUrls();
        if ($duplicateWebsiteUrlsList) {
            $idList = [];
            foreach ($duplicateWebsiteUrlsList as $duplicateWebsiteUrl) {
                $idList[] = $duplicateWebsiteUrl->website_url;
            }
            $bindings['GamesCompanyList'] = $this->getServicePartner()->getGamesCompaniesWithWebsiteUrlList($idList);
        }

        return view('staff.partners.games-company.list', $bindings);
    }

    public function show(Partner $partner)
    {
        if (!$partner->isGamesCompany()) abort(404);

        $bindings = $this->getBindingsPartnersSubpage($partner->name);

        $partnerId = $partner->id;

        $gameDevList = $this->getServiceGameDeveloper()->getGamesByDeveloper($partnerId, false);
        $gamePubList = $this->getServiceGamePublisher()->getGamesByPublisher($partnerId, false);

        $mergedGameList = $this->getServicePartner()->getMergedGameList($gameDevList, $gamePubList);

        $bindings['PartnerData'] = $partner;
        $bindings['PartnerId'] = $partnerId;

        $bindings['OutreachList'] = $this->getServicePartnerOutreach()->getByPartnerId($partnerId);

        $bindings['MergedGameList'] = $mergedGameList;

        return view('staff.partners.games-company.show', $bindings);
    }

    public function devsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $bindings = $this->getBindingsPartnersSubpage('Outreach targets: Developers with unranked games', $tableSort);

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getDevelopersWithUnrankedGames();

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function pubsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $bindings = $this->getBindingsPartnersSubpage('Outreach targets: Publishers with unranked games', $tableSort);

        $bindings['GamesCompanyList'] = $this->getServicePartner()->getPublishersWithUnrankedGames();
        $bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsGamesCompaniesSubpage('Add games company');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            if ($request->is_low_quality == 'on') {
                $isLowQuality = 1;
            } else {
                $isLowQuality = 0;
            }

            $partner = GamesCompanyFactory::createActive(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $isLowQuality
            );
            $partner->save();

            return redirect(route('staff.partners.games-company.show', ['partner' => $partner]));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.partners.games-company.add', $bindings);
    }

    public function edit($partnerId)
    {
        $bindings = $this->getBindingsGamesCompaniesSubpage('Edit games company');

        $partnerData = $this->getServicePartner()->find($partnerId);
        if (!$partnerData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            if ($request->is_low_quality == 'on') {
                $isLowQuality = 1;
            } else {
                $isLowQuality = 0;
            }
            $this->getServicePartner()->editGamesCompany(
                $partnerData, $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $isLowQuality
            );

            $this->gameQualityFilter->updateGamesByPartner($partnerData, $isLowQuality);

            return redirect(route('staff.partners.games-company.show', ['partner' => $partnerData]));

        } else {

            $bindings['FormMode'] = 'edit';

        }

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
        $bindings = $this->getBindingsGamesCompaniesSubpage('Delete games company');

        $partnerData = $this->getServicePartner()->find($partnerId);
        if (!$partnerData) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the record to be deleted.
        $gameDevelopers = $this->getServiceGameDeveloper()->getByDeveloperId($partnerId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Games company is marked as the developer for '.count($gameDevelopers).' game(s)';
        }
        $gamePublishers = $this->getServiceGamePublisher()->getByPublisherId($partnerId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Games company is marked as the publisher for '.count($gamePublishers).' game(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServicePartner()->deleteGamesCompany($partnerId);

            return redirect(route('staff.partners.games-company.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.partners.games-company.delete', $bindings);
    }

}
