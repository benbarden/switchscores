<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Laravel\Socialite\Facades\Socialite;

use App\Domain\User\Repository as UserRepository;

use App\Events\UserCreated;

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
        View::share('PageTitle', 'Login');
        View::share('TopTitle', 'Login');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('members.index');
        }

        return view('auth.login');
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
        $repoUser = new UserRepository();

        $user = Socialite::driver('twitter')->user();
        $twitterUserId = $user->id;

        $siteUser = $repoUser->getByTwitterId($twitterUserId);

        if (!$siteUser) {
            // Block new Twitter signups - redirect to register page
            return redirect(route('register'))
                ->with('error', 'Twitter signup is no longer available. Please register with your email address.');
        }

        auth()->login($siteUser);
        return redirect(route('members.index'));
    }
}
