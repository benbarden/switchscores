<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\View;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('PageTitle', 'Forgot password');
        View::share('TopTitle', 'Forgot password');
        //$this->redirectTo('/');
        //$this->middleware('guest');
    }
}
