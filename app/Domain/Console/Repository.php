<?php

namespace App\Domain\Console;

use App\Models\Console;

class Repository
{
    public function consoleList()
    {
        return [
            ['id' => Console::ID_SWITCH_1, 'name' => Console::DESC_SWITCH_1],
            ['id' => Console::ID_SWITCH_2, 'name' => Console::DESC_SWITCH_2],
        ];
    }

    public function getAll()
    {
        return Console::orderBy('id')->get();
    }

    public function find($id)
    {
        return Console::find($id);
    }
}