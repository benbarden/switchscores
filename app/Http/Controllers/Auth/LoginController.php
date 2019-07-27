<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Laravel\Socialite\Facades\Socialite;

use App\Services\ServiceContainer;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/user';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProviderTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Obtain the user information.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallbackTwitter()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceUser = $serviceContainer->getUserService();

        $user = Socialite::driver('twitter')->user();
        $twitterUserId = $user->id;
        $twitterName = $user->nickname;

        $wosUser = $serviceUser->getByTwitterId($twitterUserId);

        if (!$wosUser) {
            $wosUser = $serviceUser->createFromTwitterLogin($twitterUserId, $twitterName);
            event(new UserCreated($wosUser));
        }

        // we should have a user by now
        if (!$wosUser) abort(400);

        auth()->login($wosUser);
        return redirect(route('user.index'));
    }
}
