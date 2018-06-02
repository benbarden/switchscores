<?php
namespace App\Http\Controllers;

use App\Services\ServiceContainer;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Auth;

use App\Services\GameService;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var ServiceContainer
     */
    protected $serviceContainer;

    /**
     * @var GameService
     */
    protected $serviceGame;

    /**
     * @var string
     */
    protected $region;

    public function __construct()
    {
        $this->serviceContainer = new ServiceContainer();

        $this->serviceGame = resolve('Services\GameService');

        \View::share('env', \App::environment());

        $this->region = $this->getRegion();
        \View::share('region', $this->region);

        $currentUser = Auth::user();
        $currentUserId = Auth::id();

        \View::share('auth_user', $currentUser);
        \View::share('auth_id', $currentUserId);
        \View::share('user', $currentUser);
        \View::share('uid', $currentUserId);
    }

    private function getRegion()
    {
        return 'eu';
    }
}