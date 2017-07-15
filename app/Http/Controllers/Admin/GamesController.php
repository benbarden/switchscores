<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\GameService;

class GamesController extends \App\Http\Controllers\BaseController
{
    /**
     * @var \App\Services\GameService
     */
    private $serviceClass;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:255',
        'link_title' => 'required|max:100',
    ];

    public function __construct()
    {
        $this->serviceClass = resolve('Services\GameService');
        parent::__construct();
    }

    public function showList()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Games';

        $gameList = $this->serviceClass->getAll(GameService::ORDER_NEWEST);

        $bindings['GameList'] = $gameList;

        return view('admin.games.list', $bindings);
    }

    public function add()
    {
        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $this->serviceClass->create(
                $request->title, $request->link_title, $request->release_date, $request->price_eshop,
                $request->players, $request->upcoming, $request->upcoming_date, $request->overview
            );

            return redirect(route('admin.games.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Games - Add game';
        $bindings['FormMode'] = 'add';

        return view('admin.games.add', $bindings);
    }

    public function edit($gameId)
    {
        $bindings = array();

        $gameData = $this->serviceClass->find($gameId);
        if (!$gameData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->serviceClass->edit(
                $gameData,
                $request->title, $request->link_title, $request->release_date, $request->price_eshop,
                $request->players, $request->upcoming, $request->upcoming_date, $request->overview
            );

            return redirect(route('admin.games.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Games - Edit game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        return view('admin.games.edit', $bindings);
    }
}