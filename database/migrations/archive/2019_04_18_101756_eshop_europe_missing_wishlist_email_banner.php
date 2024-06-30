<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopEuropeMissingWishlistEmailBanner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eshop_europe_games', function(Blueprint $table) {
            $table->text('wishlist_email_banner460w_image_url_s')->nullable();
            $table->text('wishlist_email_banner640w_image_url_s')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eshop_europe_games', function(Blueprint $table) {
            $table->dropColumn('wishlist_email_banner460w_image_url_s');
            $table->dropColumn('wishlist_email_banner640w_image_url_s');
        });
    }
}
