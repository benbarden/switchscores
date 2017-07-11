<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        \View::share('env', \App::environment());

        $currentUser = Auth::user();
        $currentUserId = Auth::id();
        \View::share('user', $currentUser);
        \View::share('uid', $currentUserId);
    }
}