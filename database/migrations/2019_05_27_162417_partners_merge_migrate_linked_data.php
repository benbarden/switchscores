<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Partner;

use App\Services\ReviewSiteService;
use App\Services\DeveloperService;
use App\Services\PublisherService;
use App\Services\PartnerService;

class PartnersMergeMigrateLinkedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $serviceReviewSite = resolve('Services\ReviewSiteService');
        $serviceDeveloper = resolve('Services\DeveloperService');
        $servicePublisher = resolve('Services\PublisherService');
        /* @var $serviceReviewSite ReviewSiteService */
        /* @var $serviceDeveloper DeveloperService */
        /* @var $servicePublisher PublisherService */

        // Review sites
        // No changes needed as we're keeping the same IDs
        // Keeping code here for reference
        /*
        $reviewSites = $serviceReviewSite->getAll();

        $siteMapping = [];

        foreach ($reviewSites as $reviewSite) {

            $siteId = $reviewSite->id;
            $newPartnerId = $reviewSite->new_partner_id;

            $siteMapping[$siteId] = $newPartnerId;

            //\DB::update('UPDATE feed_item_reviews SET site_id = ? WHERE site_id = ?', [$newPartnerId, $siteId]);
            //\DB::update('UPDATE partner_reviews SET site_id = ? WHERE site_id = ?', [$newPartnerId, $siteId]);
            //\DB::update('UPDATE review_links SET site_id = ? WHERE site_id = ?', [$newPartnerId, $siteId]);

        }

        // Update feed_item_reviews
        $listToUpdate = \DB::select('SELECT * FROM feed_item_reviews ORDER BY id');
        foreach ($listToUpdate as $itemToUpdate) {
            $id = $itemToUpdate->id;
            $siteId = $itemToUpdate->site_id;
            $newPartnerId = $siteMapping[$siteId];
            \DB::update('UPDATE feed_item_reviews SET site_id = ? WHERE id = ?', [$newPartnerId, $id]);
        }

        // Update partner_reviews
        $listToUpdate = \DB::select('SELECT * FROM partner_reviews ORDER BY id');
        foreach ($listToUpdate as $itemToUpdate) {
            $id = $itemToUpdate->id;
            $siteId = $itemToUpdate->site_id;
            $newPartnerId = $siteMapping[$siteId];
            \DB::update('UPDATE partner_reviews SET site_id = ? WHERE id = ?', [$newPartnerId, $id]);
        }

        // Update review_links
        $listToUpdate = \DB::select('SELECT * FROM review_links ORDER BY id');
        foreach ($listToUpdate as $itemToUpdate) {
            $id = $itemToUpdate->id;
            $siteId = $itemToUpdate->site_id;
            $newPartnerId = $siteMapping[$siteId];
            \DB::update('UPDATE review_links SET site_id = ? WHERE id = ?', [$newPartnerId, $id]);
        }
        */

        // Developers
        $developers = $serviceDeveloper->getAll();

        $devMapping = [];

        foreach ($developers as $developer) {

            $devId = $developer->id;
            $newPartnerId = $developer->new_partner_id;

            $devMapping[$devId] = $newPartnerId;

            //\DB::update('UPDATE game_developers SET developer_id = ? WHERE developer_id = ?', [$newPartnerId, $devId]);

        }

        // Update game_developers
        $listToUpdate = \DB::select('SELECT * FROM game_developers ORDER BY id');
        foreach ($listToUpdate as $itemToUpdate) {
            $id = $itemToUpdate->id;
            $oldPartnerId = $itemToUpdate->developer_id;
            $newPartnerId = $devMapping[$oldPartnerId];
            \DB::update('UPDATE game_developers SET developer_id = ? WHERE id = ?', [$newPartnerId, $id]);
        }

        // Publishers
        $publishers = $servicePublisher->getAll();

        $pubMapping = [];

        foreach ($publishers as $publisher) {

            $pubId = $publisher->id;
            $newPartnerId = $publisher->new_partner_id;

            $pubMapping[$pubId] = $newPartnerId;

            //\DB::update('UPDATE game_publishers SET publisher_id = ? WHERE publisher_id = ?', [$newPartnerId, $pubId]);

        }

        // Update game_publishers
        $listToUpdate = \DB::select('SELECT * FROM game_publishers ORDER BY id');
        foreach ($listToUpdate as $itemToUpdate) {
            $id = $itemToUpdate->id;
            $oldPartnerId = $itemToUpdate->publisher_id;
            $newPartnerId = $pubMapping[$oldPartnerId];
            \DB::update('UPDATE game_publishers SET publisher_id = ? WHERE id = ?', [$newPartnerId, $id]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
