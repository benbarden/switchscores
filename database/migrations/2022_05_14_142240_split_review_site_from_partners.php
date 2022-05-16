<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Partner;

class SplitReviewSiteFromPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_sites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status', 20);
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->text('website_url')->nullable();
            $table->string('twitter_id', 20)->nullable();
            $table->tinyInteger('rating_scale')->nullable();
            $table->integer('review_count');
            $table->date('last_review_date')->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->text('contact_email')->nullable();
            $table->text('contact_form_link')->nullable();
            $table->string('review_code_regions', 100)->nullable();
            $table->string('review_import_method', 100)->nullable();

            $table->timestamps();

            $table->index('status', 'status');
            $table->index('link_title', 'link_title');
        });

        $reviewSites = \DB::select("
            SELECT * FROM partners WHERE type_id = ?
        ", [Partner::TYPE_REVIEW_SITE]);

        if ($reviewSites) {

            foreach ($reviewSites as $site) {

                $siteData = [
                    'id' => $site->id,
                    'status' => 'Active',
                    'name' => $site->name,
                    'link_title' => $site->link_title,
                    'website_url' => $site->website_url,
                    'twitter_id' => $site->twitter_id,
                    'rating_scale' => $site->rating_scale,
                    'review_count' => $site->review_count,
                    'last_review_date' => $site->last_review_date,
                    'contact_name' => $site->contact_name,
                    'contact_email' => $site->contact_email,
                    'contact_form_link' => $site->contact_form_link,
                    'review_code_regions' => $site->review_code_regions,
                    'review_import_method' => $site->review_import_method,
                    'created_at' => $site->created_at,
                    'updated_at' => $site->updated_at,
                ];


                \DB::insert("
                    INSERT INTO review_sites(id, status, name, link_title, website_url, twitter_id, 
                                             rating_scale, review_count, last_review_date, contact_name,
                                             contact_email, contact_form_link, review_code_regions,
                                             review_import_method, created_at, updated_at)
                     VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$siteData['id'], $siteData['status'], $siteData['name'], $siteData['link_title'],
                    $siteData['website_url'], $siteData['twitter_id'], $siteData['rating_scale'],
                    $siteData['review_count'], $siteData['last_review_date'],
                    $siteData['contact_name'], $siteData['contact_email'], $siteData['contact_form_link'],
                    $siteData['review_code_regions'], $siteData['review_import_method'],
                    $siteData['created_at'], $siteData['updated_at']]);

            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_sites');
    }
}
