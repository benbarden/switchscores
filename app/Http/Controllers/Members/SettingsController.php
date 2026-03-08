<?php

namespace App\Http\Controllers\Members;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

class SettingsController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
    )
    {}

    public function show()
    {
        $pageTitle = 'Account settings';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $currentUser = resolve('User/Repository')->currentUser();
        $bindings['UserData'] = $currentUser;
        $bindings['IsTwitterLogin'] = !empty($currentUser->twitter_user_id);

        return view('members.settings', $bindings);
    }

    public function update(Request $request)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $isTwitterLogin = !empty($currentUser->twitter_user_id);

        // Build validation rules
        $rules = [
            'display_name' => 'required|string|max:100',
        ];

        // Email and password only editable for non-Twitter users
        if (!$isTwitterLogin) {
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $currentUser->id;

            // Password is optional - only validate if provided
            if ($request->filled('password')) {
                $rules['password'] = ['required', 'confirmed', Password::min(8)];
            }
        }

        $validated = $request->validate($rules);

        // Update display name
        $currentUser->display_name = $validated['display_name'];

        // Update email if not Twitter login
        if (!$isTwitterLogin && isset($validated['email'])) {
            $currentUser->email = $validated['email'];
        }

        // Update password if provided and not Twitter login
        if (!$isTwitterLogin && $request->filled('password')) {
            $currentUser->password = Hash::make($validated['password']);
        }

        $currentUser->save();

        return redirect()->route('members.settings')->with('success', 'Your settings have been updated.');
    }
}
