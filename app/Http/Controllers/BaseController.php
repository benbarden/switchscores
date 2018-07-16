<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\GameService;
use App\Services\ServiceContainer;

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

    }
}