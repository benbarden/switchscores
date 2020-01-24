<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Traits\AuthUser;

class UserProfileController extends Controller
{
    use AuthUser;
}
