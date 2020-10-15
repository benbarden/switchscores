<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Campaign;

class CampaignService
{
    public function create($name, $description, $progress, $isActive)
    {
        Category::create([
            'name' => $name,
            'description' => $description,
            'progress' => $progress,
            'is_active' => $isActive,
        ]);
    }

    public function edit(Campaign $campaign, $name, $description, $progress, $isActive)
    {
        $values = [
            'name' => $name,
            'description' => $description,
            'progress' => $progress,
            'is_active' => $isActive,
        ];

        $campaign->fill($values);
        $campaign->save();
    }

    public function delete($id)
    {
        Campaign::where('id', $id)->delete();
    }

    public function find($id)
    {
        return Campaign::find($id);
    }

    public function getAll()
    {
        return Campaign::orderBy('id', 'desc')->get();
    }

    public function getActive()
    {
        return Campaign::where('is_active', 1)->orderBy('id', 'desc')->get();
    }
}