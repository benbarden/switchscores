<?php

use App\Models\Partner;
use App\Services\DeveloperService;
use App\Services\PublisherService;
use App\Services\ReviewSiteService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PartnersMergeMigrateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->integer('new_partner_id')->nullable();
            $table->index('new_partner_id', 'new_partner_id');
        });

        Schema::table('developers', function(Blueprint $table) {
            $table->integer('new_partner_id')->nullable();
            $table->index('new_partner_id', 'new_partner_id');
        });

        Schema::table('publishers', function(Blueprint $table) {
            $table->integer('new_partner_id')->nullable();
            $table->index('new_partner_id', 'new_partner_id');
        });

        $serviceReviewSite = resolve('Services\ReviewSiteService');
        $serviceDeveloper = resolve('Services\DeveloperService');
        $servicePublisher = resolve('Services\PublisherService');
        /* @var $serviceReviewSite ReviewSiteService */
        /* @var $serviceDeveloper DeveloperService */
        /* @var $servicePublisher PublisherService */

        // Review sites
        $typeId = Partner::TYPE_REVIEW_SITE;
        $reviewSites = $serviceReviewSite->getAll();

        foreach ($reviewSites as $reviewSite) {

            $siteId = $reviewSite->id;
            if ($reviewSite->active == 'Y') {
                $status = Partner::STATUS_ACTIVE;
            } else {
                $status = Partner::STATUS_INACTIVE;
            }

            \DB::insert('
                INSERT INTO partners(id, type_id, status, name, link_title, website_url, twitter_id, 
                feed_url, feed_url_prefix, rating_scale, allow_historic_content, title_match_rule_pattern, 
                title_match_index, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $siteId, $typeId, $status, $reviewSite->name, $reviewSite->link_title, $reviewSite->url, NULL,
                $reviewSite->feed_url, $reviewSite->feed_url_prefix, $reviewSite->rating_scale,
                $reviewSite->allow_historic_content, $reviewSite->title_match_rule_pattern,
                $reviewSite->title_match_index
            ]);

            $newPartnerId = \DB::getPdo()->lastInsertId();

            \DB::update('UPDATE review_sites SET new_partner_id = ? WHERE id = ?', [$newPartnerId, $siteId]);

        }

        // Developers
        $typeId = Partner::TYPE_GAMES_COMPANY;
        $status = 1;
        $developers = $serviceDeveloper->getAll();

        foreach ($developers as $developer) {

            $devId = $developer->id;

            \DB::insert('
                INSERT INTO partners(type_id, status, name, link_title, website_url, twitter_id, 
                created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $typeId, $status, $developer->name, $developer->link_title, $developer->url, $developer->twitter_id
            ]);

            $newPartnerId = \DB::getPdo()->lastInsertId();

            \DB::update('UPDATE developers SET new_partner_id = ? WHERE id = ?', [$newPartnerId, $devId]);

        }

        // Publishers
        $typeId = Partner::TYPE_GAMES_COMPANY;
        $status = 1;
        $publishers = $servicePublisher->getAll();

        foreach ($publishers as $publisher) {

            $pubId = $publisher->id;

            \DB::insert('
                INSERT INTO partners(type_id, status, name, link_title, website_url, twitter_id, 
                created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $typeId, $status, $publisher->name, $publisher->link_title, $publisher->url, $publisher->twitter_id
            ]);

            $newPartnerId = \DB::getPdo()->lastInsertId();

            \DB::update('UPDATE publishers SET new_partner_id = ? WHERE id = ?', [$newPartnerId, $pubId]);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_sites', function(Blueprint $table) {
            $table->dropColumn('new_partner_id');
        });

        Schema::table('developers', function(Blueprint $table) {
            $table->dropColumn('new_partner_id');
        });

        Schema::table('publishers', function(Blueprint $table) {
            $table->dropColumn('new_partner_id');
        });

        \DB::statement('TRUNCATE TABLE partners');
    }
}
