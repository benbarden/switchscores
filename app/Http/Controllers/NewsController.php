<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class NewsController extends BaseController
{
    public function newsStatic($category, $title)
    {
        $bindings = array();

        if ($category == 'top-rated-new-releases') {
            if ($title == '171012') {

                $viewStatic = 'news.static.topRatedNewReleases.171012';
                $topTitle = 'Top Rated: New Releases - 12th October, 2017';
                $pageTitle = 'Top Rated: New Releases - 12th October, 2017';

                $topRatedNewReleases = [];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(73), 'Rating' => '9.0', 'ReviewCount' => '11',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(98), 'Rating' => '9.0', 'ReviewCount' => '3',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(330), 'Rating' => '8.9', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(335), 'Rating' => '8.7', 'ReviewCount' => '3',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(142), 'Rating' => '8.6', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(138), 'Rating' => '8.0', 'ReviewCount' => '6',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(144), 'Rating' => '8.0', 'ReviewCount' => '10',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(87), 'Rating' => '7.9', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(248), 'Rating' => '7.9', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(259), 'Rating' => '7.7', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(243), 'Rating' => '7.7', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(271), 'Rating' => '7.6', 'ReviewCount' => '5',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(329), 'Rating' => '7.6', 'ReviewCount' => '4',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(103), 'Rating' => '7.6', 'ReviewCount' => '6',
                ];
                $topRatedNewReleases[] = [
                    'Game' => $this->serviceGame->find(252), 'Rating' => '7.5', 'ReviewCount' => '5',
                ];

                $bindings['TopRatedNewReleases'] = $topRatedNewReleases;

            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view($viewStatic, $bindings);
    }
}
