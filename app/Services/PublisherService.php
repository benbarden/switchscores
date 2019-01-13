<?php


namespace App\Services;

use App\Publisher;


class PublisherService
{
    /**
     * @param $name
     * @param $linkTitle
     * @param $websiteUrl
     * @param $twitterId
     * @return Publisher
     */
    public function create(
        $name, $linkTitle, $websiteUrl, $twitterId
    )
    {
        return Publisher::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
        ]);
    }

    /**
     * @param Publisher $publisherData
     * @param $name
     * @param $linkTitle
     * @param $websiteUrl
     * @param $twitterId
     * @return void
     */
    public function edit(
        Publisher $publisherData, $name, $linkTitle, $websiteUrl, $twitterId
    )
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
        ];

        $publisherData->fill($values);
        $publisherData->save();
    }

    public function delete($publisherId)
    {
        Publisher::where('id', $publisherId)->delete();
    }

    // ********************************************************** //

    public function find($id)
    {
        return Publisher::find($id);
    }

    public function getAll()
    {
        return Publisher::orderBy('name', 'asc')->get();
    }

    public function getByLinkTitle($linkTitle)
    {
        return Publisher::where('link_title', $linkTitle)->first();
    }
}