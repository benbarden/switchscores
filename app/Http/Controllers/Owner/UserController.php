<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\User\Repository as UserRepository;
use App\Domain\Partner\Repository as PartnerRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\UserRole;

class UserController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'display_name' => 'required',
        //'email' => 'required',
    ];

    protected $viewBreadcrumbs;
    protected $repoUser;
    protected $repoPartner;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        UserRepository $repoUser,
        PartnerRepository $repoPartner
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoUser = $repoUser;
        $this->repoPartner = $repoPartner;
    }

    public function showList()
    {
        $bindings = $this->getBindings('Users');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Users');

        $bindings['UserList'] = $this->repoUser->getAll();

        return view('owner.user.list', $bindings);
    }

    public function showUser($userId)
    {
        $userData = $this->repoUser->find($userId);
        if (!$userData) abort(404);

        $displayName = $userData->display_name;

        $bindings = $this->getBindings('View user: '.$displayName);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->usersSubpage('View user: '.$displayName);

        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;

        $statsQuickReviews = $this->getServiceQuickReview()->getAllByUser($userId);
        $statsCollection = $this->getServiceUserGamesCollection()->getByUser($userId);

        $bindings['StatsQuickReviews'] = count($statsQuickReviews);
        $bindings['StatsGameCategorySuggestions'] = $this->getServiceDbEditGame()->countApprovedCategoryEditsByUser($userId);
        $bindings['StatsCollection'] = count($statsCollection);

        return view('owner.user.view', $bindings);
    }

    public function editUser($userId)
    {
        $bindings = $this->getBindings('Edit user');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->usersSubpage('Edit user');

        $userData = $this->getServiceUser()->find($userId);
        if (!$userData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $displayName = $request->display_name;
            $email = $request->email;
            $twitterUserId = $request->twitter_user_id;
            $partnerId = $request->partner_id;
            $isStaff = $request->is_staff;
            $isDeveloper = $request->is_developer;
            $gamesCompanyId = $request->games_company_id;

            $this->repoUser->edit($userData, $displayName, $email, $partnerId, $twitterUserId,
                $isStaff, $isDeveloper, $gamesCompanyId);

            // Clear roles
            $userData->setRoles([]);

            if (isset($request->role_item)) {

                foreach ($request->role_item as $roleKey => $roleValue) {

                    $role = UserRole::getRoleFromId($roleKey);
                    $userData->addRole($role);

                }
                $userData->save();

            }

            return redirect(route('owner.user.view', ['userId' => $userId]));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;

        $bindings['PartnerList'] = $this->repoPartner->reviewSitesActive();
        $bindings['GamesCompanyList'] = $this->repoPartner->gamesCompanies();

        $bindings['RoleList'] = UserRole::getRoleList();

        $userRoleList = $userData->user_roles;
        if ($userRoleList) {
            $userRoleListForView = [];
            foreach ($userRoleList as $userRole) {
                $roleId = UserRole::getIdFromName($userRole);
                $userRoleListForView[] = ['id' => $roleId, 'role' => $userRole];
            }
            $bindings['UserRoleList'] = $userRoleListForView;
        }

        return view('owner.user.edit', $bindings);
    }

    public function deleteUser($userId)
    {
        $bindings = $this->getBindings('Delete user');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->usersSubpage('Delete user');

        $userData = $this->repoUser->find($userId);
        if (!$userData) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the user to be deleted.
        $reviewLinks = $this->getServiceReviewLink()->getByUser($userId);
        if (count($reviewLinks) > 0) {
            $customErrors[] = 'User has created '.count($reviewLinks).' review link(s)';
        }
        $quickReviews = $this->getServiceQuickReview()->getAllByUser($userId);
        if (count($quickReviews) > 0) {
            $customErrors[] = 'User has created '.count($quickReviews).' quick review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceUserGamesCollection()->deleteByUserId($userId);
            $this->repoUser->deleteUser($userId);

            // Done

            return redirect(route('owner.user.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('owner.user.delete', $bindings);
    }

}