<?php

namespace App\Http\Controllers\Owner;

use App\UserRole;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class UserController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'display_name' => 'required',
        //'email' => 'required',
    ];

    public function showList()
    {
        $serviceUser = $this->getServiceUser();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Users';
        $bindings['PageTitle'] = 'Users';

        $userList = $serviceUser->getAll();

        $bindings['UserList'] = $userList;

        return view('owner.user.list', $bindings);
    }

    public function showUser($userId)
    {
        $serviceUser = $this->getServiceUser();
        $serviceCollection = $this->getServiceUserGamesCollection();

        $userData = $serviceUser->find($userId);
        if (!$userData) abort(404);

        $displayName = $userData->display_name;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - View user - '.$displayName;
        $bindings['PageTitle'] = 'View user - '.$displayName;

        $bindings['UserData'] = $userData;

        $bindings['CollectionList'] = $serviceCollection->getByUser($userId);
        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        return view('owner.user.view', $bindings);
    }

    public function editUser($userId)
    {
        $serviceUser = $this->getServiceUser();
        $servicePartner = $this->getServicePartner();

        $userData = $serviceUser->find($userId);
        if (!$userData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $displayName = $request->display_name;
            $email = $request->email;
            $twitterUserId = $request->twitter_user_id;
            $partnerId = $request->partner_id;
            $isStaff = $request->is_staff;

            $serviceUser->edit($userData, $displayName, $email, $partnerId, $twitterUserId, $isStaff);

            // Clear roles
            $userData->setRoles([]);

            if (isset($request->role_item)) {

                foreach ($request->role_item as $roleKey => $roleValue) {

                    $role = UserRole::getRoleFromId($roleKey);
                    $userData->addRole($role);

                }
                $userData->save();

            }

            return redirect(route('owner.user.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit user';
        $bindings['PageTitle'] = 'Edit user';
        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;

        $bindings['PartnerList'] = $servicePartner->getAllForUserAssignment();

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
        // Core
        $serviceUser = $this->getServiceUser();

        // Validation
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceQuickReview = $this->getServiceQuickReview();

        // Deletion
        $serviceUserGamesCollection = $this->getServiceUserGamesCollection();

        $userData = $serviceUser->find($userId);
        if (!$userData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the user to be deleted.
        $reviewLinks = $serviceReviewLink->getByUser($userId);
        if (count($reviewLinks) > 0) {
            $customErrors[] = 'User has created '.count($reviewLinks).' review link(s)';
        }
        $quickReviews = $serviceQuickReview->getAllByUser($userId);
        if (count($quickReviews) > 0) {
            $customErrors[] = 'User has created '.count($quickReviews).' quick review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceUserGamesCollection->deleteByUserId($userId);
            $serviceUser->deleteUser($userId);

            // Done

            return redirect(route('owner.user.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Users - Delete user';
        $bindings['PageTitle'] = 'Delete user';
        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('owner.user.delete', $bindings);
    }

}