<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EshopEuropeGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eshop_europe_games', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('fs_id');
            $table->string('change_date', 30)->nullable();
            $table->text('url')->nullable();
            $table->string('type', 30)->nullable();
            $table->text('dates_released_dts')->nullable();                   // Array - convert to JSON
            $table->tinyInteger('club_nintendo')->nullable();                 // Boolean
            $table->text('language_availability')->nullable();                // Array - convert to JSON
            $table->tinyInteger('price_has_discount_b')->nullable();          // Boolean
            $table->string('pretty_date_s', 30)->nullable();                  // Date in UK format
            $table->tinyInteger('play_mode_tv_mode_b')->nullable();           // Boolean
            $table->decimal('price_discount_percentage_f', 4, 1)->nullable();
            $table->text('title')->nullable();
            $table->text('sorting_title')->nullable();
            $table->text('copyright_s')->nullable();
            $table->text('gift_finder_carousel_image_url_s')->nullable();
            $table->integer('players_to')->nullable();
            $table->tinyInteger('play_mode_handheld_mode_b')->nullable();     // Boolean
            $table->text('product_code_txt')->nullable();                     // Array - convert to JSON
            $table->text('image_url_sq_s')->nullable();
            $table->text('playable_on_txt')->nullable();                      // Array - convert to JSON
            $table->text('pretty_game_categories_txt')->nullable();           // Array - convert to JSON
            $table->text('gift_finder_wishlist_image_url_s')->nullable();
            $table->string('pg_s', 30)->nullable();
            $table->text('gift_finder_detail_page_image_url_s')->nullable();
            $table->text('compatible_controller')->nullable();                // Array - convert to JSON
            $table->text('game_category')->nullable();                        // Array - convert to JSON
            $table->text('image_url')->nullable();
            $table->text('system_names_txt')->nullable();                     // Array - convert to JSON
            $table->string('pretty_agerating_s', 30)->nullable();
            $table->string('originally_for_t', 30)->nullable();
            $table->tinyInteger('cloud_saves_b')->nullable();                 // Boolean
            $table->tinyInteger('digital_version_b')->nullable();             // Boolean
            $table->text('title_extras_txt')->nullable();                     // Array - convert to JSON
            $table->string('age_rating_type', 30)->nullable();
            $table->text('image_url_h2x1_s')->nullable();
            $table->text('system_type')->nullable();                          // Array - convert to JSON
            $table->integer('age_rating_sorting_i')->nullable();
            $table->text('game_categories_txt')->nullable();                  // Array - convert to JSON
            $table->decimal('price_sorting_f', 6, 2)->nullable();
            $table->tinyInteger('play_mode_tabletop_mode_b')->nullable();     // Boolean
            $table->text('publisher')->nullable();
            $table->decimal('price_lowest_f', 6, 2)->nullable();
            $table->string('age_rating_value', 30)->nullable();
            $table->tinyInteger('physical_version_b')->nullable();            // Boolean
            $table->text('excerpt')->nullable();
            $table->text('nsuid_txt')->nullable();                            // Array - convert to JSON
            $table->string('date_from', 30)->nullable();                      // Timestamp

            // Additional fields
            $table->tinyInteger('hd_rumble_b')->nullable();                   // Boolean
            $table->string('multiplayer_mode', 30)->nullable();
            $table->tinyInteger('ir_motion_camera_b')->nullable();            // Boolean
            $table->text('gift_finder_description_s')->nullable();
            $table->integer('players_from')->nullable();
            $table->text('gift_finder_detail_page_store_link_s')->nullable();
            $table->tinyInteger('demo_availability')->nullable();             // Boolean
            $table->tinyInteger('paid_subscription_required_b')->nullable();  // Boolean
            $table->tinyInteger('internet')->nullable();                      // Boolean
            $table->tinyInteger('add_on_content_b')->nullable();              // Boolean
            $table->tinyInteger('reg_only_hidden')->nullable();               // Boolean
            $table->tinyInteger('play_coins')->nullable();                    // Boolean
            $table->tinyInteger('ranking_b')->nullable();                     // Boolean
            $table->tinyInteger('match_play_b')->nullable();                  // Boolean
            $table->text('developer')->nullable();
            $table->tinyInteger('near_field_comm_b')->nullable();             // Boolean
            $table->tinyInteger('indie_b')->nullable();                       // Boolean
            $table->string('priority', 30)->nullable();                       // Timestamp
            $table->text('game_series_txt')->nullable();                      // Array - convert to JSON
            $table->text('game_series_t')->nullable();
            $table->tinyInteger('local_play')->nullable();                    // Boolean
            $table->tinyInteger('coop_play_b')->nullable();                   // Boolean
            $table->tinyInteger('off_tv_play_b')->nullable();                 // Boolean
            $table->text('image_url_tm_s')->nullable();
            $table->text('datasize_readable_txt')->nullable();                // Array - convert to JSON
            $table->tinyInteger('mii_support')->nullable();                   // Boolean
            $table->tinyInteger('voice_chat_b')->nullable();                  // Boolean
            $table->tinyInteger('download_play')->nullable();                 // Boolean

            $table->string('version', 30)->nullable();                        // Field: _version_

            $table->index('fs_id', 'fs_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eshop_europe_games');
    }
}
