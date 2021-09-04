<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\ResetsPasswords;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

use App\User;

class ResetPasswordController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResetsPasswords;
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */


    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('PageTitle', 'Reset password');
        View::share('TopTitle', 'Reset password');
        //$this->redirectTo('/');
        //$this->middleware('guest');
    }

}
