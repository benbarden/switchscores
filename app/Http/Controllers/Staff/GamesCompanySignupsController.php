<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\GamesCompanySignup\Repository as GamesCompanySignupRepository;

class GamesCompanySignupsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GamesCompanySignupRepository $repoGamesCompanySignup
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Games company signups';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompanySignupsList())->bindings;

        $bindings['SignupList'] = $this->repoGamesCompanySignup->getAll();

        return view('staff.games-company-signups.index', $bindings);
    }
}
