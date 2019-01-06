<?php


namespace App\Services;

use App\Developer;


class DeveloperService
{
    /**
     * @param $name
     * @param $linkTitle
     * @param $websiteUrl
     * @param $twitterId
     * @return Developer
     */
    public function create(
        $name, $linkTitle, $websiteUrl, $twitterId
    )
    {
        return Developer::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
        ]);
    }

    /**
     * @param Developer $developerData
     * @param $name
     * @param $linkTitle
     * @param $websiteUrl
     * @param $twitterId
     * @return void
     */
    public function edit(
        Developer $developerData, $name, $linkTitle, $websiteUrl, $twitterId
    )
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
        ];

        $developerData->fill($values);
        $developerData->save();
    }

    public function delete($developerId)
    {
        Developer::where('id', $developerId)->delete();
    }

    // ********************************************************** //

    public function find($id)
    {
        return Developer::find($id);
    }

    public function getAll()
    {
        return Developer::orderBy('created_at', 'desc')->get();
    }
}