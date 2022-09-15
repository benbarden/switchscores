<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertPartnersToGamesCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->text('website_url')->nullable();
            $table->string('twitter_id', 20)->nullable();
            $table->integer('last_outreach_id')->nullable();
            $table->tinyInteger('is_low_quality')->default(0);

            $table->timestamps();

            $table->index('link_title', 'link_title');
        });

        $gamesCompanies = \DB::select("SELECT * FROM partners WHERE type_id = 2");

        if ($gamesCompanies) {

            foreach ($gamesCompanies as $item) {

                $siteData = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'link_title' => $item->link_title,
                    'website_url' => $item->website_url,
                    'twitter_id' => $item->twitter_id,
                    'last_outreach_id' => $item->last_outreach_id,
                    'is_low_quality' => $item->is_low_quality,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];

                \DB::insert("
                    INSERT INTO games_companies(id, name, link_title, website_url, twitter_id, 
                                             last_outreach_id, is_low_quality, created_at, updated_at)
                     VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$siteData['id'], $siteData['name'], $siteData['link_title'],
                    $siteData['website_url'], $siteData['twitter_id'],
                    $siteData['last_outreach_id'], $siteData['is_low_quality'],
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
        Schema::table('games_companies', function (Blueprint $table) {
            Schema::dropIfExists('games_companies');
        });
    }
}
