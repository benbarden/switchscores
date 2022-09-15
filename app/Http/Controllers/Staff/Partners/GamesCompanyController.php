<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\GamesCompany;
use App\Factories\GamesCompanyFactory;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;

use App\Traits\StaffView;
use App\Traits\SwitchServices;

class GamesCompanyController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $gameQualityFilter;
    private $repoGamesCompany;
    private $statsGamesCompany;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        GameQualityFilter $gameQualityFilter,
        GamesCompanyRepository $repoGamesCompany,
        GamesCompanyStats $statsGamesCompany
    )
    {
        $this->gameQualityFilter = $gameQualityFilter;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->statsGamesCompany = $statsGamesCompany;
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

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutTwitterIds();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies without website URLs', "[ 0, 'desc']");

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutWebsiteUrls();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function duplicateTwitterIds()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies with duplicate Twitter Ids', "[ 0, 'desc']");

        $duplicateTwitterIdsList = $this->statsGamesCompany->getDuplicateTwitterIds();
        if ($duplicateTwitterIdsList) {
            $idList = [];
            foreach ($duplicateTwitterIdsList as $duplicateTwitterId) {
                $idList[] = $duplicateTwitterId->twitter_id;
            }
            $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithTwitterIdList($idList);
        }

        return view('staff.partners.games-company.list', $bindings);
    }

    public function duplicateWebsiteUrls()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games companies with duplicate website URLs', "[ 0, 'desc']");

        $duplicateWebsiteUrlsList = $this->statsGamesCompany->getDuplicateWebsiteUrls();
        if ($duplicateWebsiteUrlsList) {
            $idList = [];
            foreach ($duplicateWebsiteUrlsList as $duplicateWebsiteUrl) {
                $idList[] = $duplicateWebsiteUrl->website_url;
            }
            $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithWebsiteUrlList($idList);
        }

        return view('staff.partners.games-company.list', $bindings);
    }

    public function show(GamesCompany $gamesCompany)
    {
        $bindings = $this->getBindingsPartnersSubpage($gamesCompany->name);

        $gamesCompanyId = $gamesCompany->id;

        $gameDevList = $this->getServiceGameDeveloper()->getGamesByDeveloper($gamesCompanyId, false);
        $gamePubList = $this->getServiceGamePublisher()->getGamesByPublisher($gamesCompanyId, false);

        $mergedGameList = $this->repoGamesCompany->getMergedGameList($gameDevList, $gamePubList);

        $bindings['GamesCompany'] = $gamesCompany;
        $bindings['GamesCompanyId'] = $gamesCompanyId;

        $bindings['OutreachList'] = $this->getServicePartnerOutreach()->getByPartnerId($gamesCompanyId);

        $bindings['MergedGameList'] = $mergedGameList;

        return view('staff.partners.games-company.show', $bindings);
    }

    public function devsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $bindings = $this->getBindingsPartnersSubpage('Outreach targets: Developers with unranked games', $tableSort);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getDevelopersWithUnrankedGames();

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function pubsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $bindings = $this->getBindingsPartnersSubpage('Outreach targets: Publishers with unranked games', $tableSort);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getPublishersWithUnrankedGames();
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

            return redirect(route('staff.partners.games-company.show', ['gamesCompany' => $partner]));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.partners.games-company.add', $bindings);
    }

    public function edit($gamesCompanyId)
    {
        $bindings = $this->getBindingsGamesCompaniesSubpage('Edit games company');

        $gamesCompany = $this->repoGamesCompany->find($gamesCompanyId);
        if (!$gamesCompany) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            if ($request->is_low_quality == 'on') {
                $isLowQuality = 1;
            } else {
                $isLowQuality = 0;
            }
            $this->repoGamesCompany->editGamesCompany(
                $gamesCompany, $request->name, $request->link_title, $request->website_url, $request->twitter_id,
                $isLowQuality
            );

            $this->gameQualityFilter->updateGamesByPartner($gamesCompany, $isLowQuality);

            return redirect(route('staff.partners.games-company.show', ['gamesCompany' => $gamesCompany]));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;

        $statusList = [];
        $statusList[] = ['id' => 0, 'title' => 'Pending'];
        $statusList[] = ['id' => 1, 'title' => 'Active'];
        $statusList[] = ['id' => 9, 'title' => 'Inactive'];

        $bindings['StatusList'] = $statusList;

        return view('staff.partners.games-company.edit', $bindings);
    }

    public function delete($gamesCompanyId)
    {
        $bindings = $this->getBindingsGamesCompaniesSubpage('Delete games company');

        $gamesCompany = $this->repoGamesCompany->find($gamesCompanyId);
        if (!$gamesCompany) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the record to be deleted.
        $gameDevelopers = $this->getServiceGameDeveloper()->getByDeveloperId($gamesCompanyId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Games company is marked as the developer for '.count($gameDevelopers).' game(s)';
        }
        $gamePublishers = $this->getServiceGamePublisher()->getByPublisherId($gamesCompanyId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Games company is marked as the publisher for '.count($gamePublishers).' game(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoGamesCompany->deleteGamesCompany($gamesCompanyId);

            return redirect(route('staff.partners.games-company.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.partners.games-company.delete', $bindings);
    }

}
