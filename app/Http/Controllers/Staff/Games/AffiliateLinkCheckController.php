<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\AffiliateCodes\UsLinkCheckSummary;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\Repository\AmazonLinkCheckRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

/**
 * The Amazon US link check's problems for staff to work through, from the nightly
 * amazon:check-us-links run.
 */
class AffiliateLinkCheckController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private AmazonLinkCheckRepository $repoLinkCheck,
        private UsLinkCheckSummary $summary,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Amazon US link check';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesAffiliatesSubpage($pageTitle))->bindings;

        $bindings['LinkCheck'] = $this->summary->build();
        $bindings['Groups'] = UsLinkCheckSummary::GROUPS;

        return view('staff.games.affiliates.us-link-check', $bindings);
    }

    public function markOk($gameId)
    {
        $game = $this->repoGame->findWithoutCache($gameId);
        if (!$game) abort(404);

        $this->repoLinkCheck->markUsLinkOk($game);

        return redirect(route('staff.games.affiliates.usLinkCheck'));
    }

    public function undoOk($gameId)
    {
        $game = $this->repoGame->findWithoutCache($gameId);
        if (!$game) abort(404);

        $this->repoLinkCheck->undoUsLinkOk($game);

        return redirect(route('staff.games.affiliates.usLinkCheck'));
    }
}
