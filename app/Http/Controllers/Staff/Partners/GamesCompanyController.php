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
use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;

use App\Traits\SwitchServices;

class GamesCompanyController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $gameQualityFilter;
    private $repoGamesCompany;
    private $statsGamesCompany;
    private $repoPartnerOutreach;

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
        GamesCompanyStats $statsGamesCompany,
        PartnerOutreachRepository $repoPartnerOutreach
    )
    {
        $this->gameQualityFilter = $gameQualityFilter;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->statsGamesCompany = $statsGamesCompany;
        $this->repoPartnerOutreach = $repoPartnerOutreach;
    }

    public function showList()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getAll();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function normalQuality()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->normalQuality();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function lowQuality()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->lowQuality();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutTwitterIds()
    {
        $pageTitle = 'Games companies without Twitter IDs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutTwitterIds();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $pageTitle = 'Games companies without website URLs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutWebsiteUrls();

        return view('staff.partners.games-company.list', $bindings);
    }

    public function duplicateTwitterIds()
    {
        $pageTitle = 'Games companies with duplicate Twitter IDs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $pageTitle = 'Games companies with duplicate website URLs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $pageTitle = $gamesCompany->name;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $gamesCompanyId = $gamesCompany->id;

        $gameDevList = $this->getServiceGameDeveloper()->getGamesByDeveloper($gamesCompanyId, false);
        $gamePubList = $this->getServiceGamePublisher()->getGamesByPublisher($gamesCompanyId, false);

        $mergedGameList = $this->repoGamesCompany->getMergedGameList($gameDevList, $gamePubList);

        $bindings['GamesCompany'] = $gamesCompany;
        $bindings['GamesCompanyId'] = $gamesCompanyId;

        $bindings['OutreachList'] = $this->repoPartnerOutreach->byPartnerId($gamesCompanyId);

        $bindings['MergedGameList'] = $mergedGameList;

        return view('staff.partners.games-company.show', $bindings);
    }

    public function devsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $pageTitle = 'Outreach targets: Developers with unranked games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getDevelopersWithUnrankedGames();

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function pubsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $pageTitle = 'Outreach targets: Publishers with unranked games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getPublishersWithUnrankedGames();
        $bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.partners.games-company.list-unranked', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add games company';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersGamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $pageTitle = 'Edit games company';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersGamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $pageTitle = 'Delete games company';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersGamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
