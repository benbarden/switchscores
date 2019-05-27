<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartnersMerge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id');
            $table->integer('status');
            $table->string('name', 100);
            $table->string('link_title', 100);
            $table->text('website_url')->nullable();
            $table->string('twitter_id', 20)->nullable();
            $table->string('feed_url')->nullable();
            $table->string('feed_url_prefix', 50)->nullable();
            $table->tinyInteger('rating_scale')->nullable();
            $table->tinyInteger('allow_historic_content')->nullable();
            $table->text('title_match_rule_pattern')->nullable();
            $table->integer('title_match_index')->nullable();

            $table->timestamps();

            $table->index('type_id', 'type_id');
            $table->index('status', 'status');
            $table->index('link_title', 'link_title');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->integer('partner_id')->nullable();
            $table->index('partner_id', 'partner_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('partner_id');
        });
        Schema::dropIfExists('partners');
    }
}
