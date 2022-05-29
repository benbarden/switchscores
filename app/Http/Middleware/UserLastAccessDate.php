<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use App\Domain\User\Repository as UserRepository;

class UserLastAccessDate
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            $dtNow = new \DateTime('now');
            $todaysDate = $dtNow->format('Y-m-d');
            if ($user->last_access_date != $todaysDate) {
                $repoUser = new UserRepository();
                $repoUser->setLastAccessDate($user, $todaysDate);
            }
        }

        return $next($request);
    }
}
