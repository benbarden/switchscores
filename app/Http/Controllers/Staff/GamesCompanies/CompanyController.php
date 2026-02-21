<?php

namespace App\Http\Controllers\Staff\GamesCompanies;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;
use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;
use App\Factories\GamesCompanyFactory;
use App\Models\GamesCompany;

class CompanyController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameQualityFilter $gameQualityFilter,
        private GamesCompanyRepository $repoGamesCompany,
        private GamesCompanyStats $statsGamesCompany,
        private PartnerOutreachRepository $repoPartnerOutreach,
        private GamePublisherRepository $repoGamePublisher,
        private GamePublisherDbQueries $dbGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper,
        private GameDeveloperDbQueries $dbGameDeveloper
    )
    {
    }

    public function show(GamesCompany $gamesCompany)
    {
        $pageTitle = $gamesCompany->name;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $gamesCompanyId = $gamesCompany->id;

        // Get full lists for total count
        $gameDevListAll = $this->dbGameDeveloper->getGamesByDeveloper($gamesCompanyId, false);
        $gamePubListAll = $this->dbGamePublisher->getGamesByPublisher($gamesCompanyId, false);
        $mergedGameListAll = $this->repoGamesCompany->getMergedGameList($gameDevListAll, $gamePubListAll);
        $totalGameCount = count($mergedGameListAll);

        // Get limited lists for display (10 most recent)
        $gameDevList = $this->dbGameDeveloper->getGamesByDeveloper($gamesCompanyId, false, 10);
        $gamePubList = $this->dbGamePublisher->getGamesByPublisher($gamesCompanyId, false, 10);
        $mergedGameList = $this->repoGamesCompany->getMergedGameList($gameDevList, $gamePubList);
        // Sort by release date and limit to 10
        usort($mergedGameList, fn($a, $b) => strcmp($b->eu_release_date ?? '', $a->eu_release_date ?? ''));
        $mergedGameList = array_slice($mergedGameList, 0, 10);

        $bindings['GamesCompany'] = $gamesCompany;
        $bindings['GamesCompanyId'] = $gamesCompanyId;

        $bindings['OutreachList'] = $this->repoPartnerOutreach->byPartnerId($gamesCompanyId);

        $bindings['MergedGameList'] = $mergedGameList;
        $bindings['TotalGameCount'] = $totalGameCount;

        return view('staff.games-companies.company.show', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add games company';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

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
                $isLowQuality, $request->email, $request->threads_id, $request->bluesky_id
            );
            $partner->save();

            return redirect(route('staff.games-companies.show', ['gamesCompany' => $partner]));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.games-companies.company.add', $bindings);
    }

    public function edit($gamesCompanyId)
    {
        $pageTitle = 'Edit games company';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

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
                $isLowQuality, $request->email, $request->threads_id, $request->bluesky_id
            );

            if ($request->update_existing_games == 'on') {
                $this->gameQualityFilter->updateGamesByPartner($gamesCompany, $isLowQuality);
            }

            return redirect(route('staff.games-companies.show', ['gamesCompany' => $gamesCompany]));

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

        return view('staff.games-companies.company.edit', $bindings);
    }

    public function delete($gamesCompanyId)
    {
        $pageTitle = 'Delete games company';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $gamesCompany = $this->repoGamesCompany->find($gamesCompanyId);
        if (!$gamesCompany) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the record to be deleted.
        $gameDevelopers = $this->repoGameDeveloper->byDeveloperId($gamesCompanyId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Games company is marked as the developer for '.count($gameDevelopers).' game(s)';
        }
        $gamePublishers = $this->repoGamePublisher->byPublisherId($gamesCompanyId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Games company is marked as the publisher for '.count($gamePublishers).' game(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoGamesCompany->deleteGamesCompany($gamesCompanyId);

            return redirect(route('staff.games-companies.dashboard'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.games-companies.company.delete', $bindings);
    }

}
