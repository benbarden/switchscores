<?php


namespace App\Domain\GameCollection;

use App\GameCollection;

class Repository
{
    public function create($name, $linkTitle)
    {
        GameCollection::create([
            'name' => $name,
            'link_title' => $linkTitle,
        ]);
    }

    public function edit(GameCollection $collection, $name, $linkTitle)
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function delete($id)
    {
        GameCollection::where('id', $id)->delete();
    }

    public function find($id)
    {
        return GameCollection::find($id);
    }

    public function getAll()
    {
        return GameCollection::orderBy('name', 'asc')->get();
    }

    public function getByName($name)
    {
        return GameCollection::where('name', $name)->first();
    }

    public function getByLinkTitle($linkTitle)
    {
        return GameCollection::where('link_title', $linkTitle)->first();
    }
}