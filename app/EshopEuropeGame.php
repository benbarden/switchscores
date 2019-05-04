<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EshopEuropeGame extends Model
{
    /**
     * @var string
     */
    protected $table = 'eshop_europe_games';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        '_version_',
        'age_rating_sorting_i',
        'age_rating_type',
        'age_rating_value',
        'change_date',
        'cloud_saves_b',
        'club_nintendo',
        'compatible_controller',
        'copyright_s',
        'date_from',
        'dates_released_dts',
        'digital_version_b',
        'excerpt',
        'fs_id',
        'game_categories_txt',
        'game_category',
        'gift_finder_carousel_image_url_s',
        'gift_finder_detail_page_image_url_s',
        'gift_finder_wishlist_image_url_s',
        'image_url',
        'image_url_h2x1_s',
        'image_url_sq_s',
        'language_availability',
        'nsuid_txt',
        'originally_for_t',
        'pg_s',
        'physical_version_b',
        'play_mode_handheld_mode_b',
        'play_mode_tabletop_mode_b',
        'play_mode_tv_mode_b',
        'playable_on_txt',
        'players_to',
        'pretty_agerating_s',
        'pretty_date_s',
        'pretty_game_categories_txt',
        'price_discount_percentage_f',
        'price_has_discount_b',
        'price_lowest_f',
        'price_sorting_f',
        'product_code_txt',
        'publisher',
        'sorting_title',
        'system_names_txt',
        'system_type',
        'title',
        'title_extras_txt',
        'type',
        'url',

        'hd_rumble_b',
        'multiplayer_mode',
        'ir_motion_camera_b',
        'gift_finder_description_s',
        'players_from',
        'gift_finder_detail_page_store_link_s',
        'demo_availability',
        'paid_subscription_required_b',
        'internet',
        'add_on_content_b',
        'reg_only_hidden',
        'play_coins',
        'ranking_b',
        'match_play_b',
        'developer',
        'near_field_comm_b',
        'indie_b',
        'priority',
        'game_series_txt',
        'game_series_t',
        'local_play',
        'coop_play_b',
        'off_tv_play_b',
        'image_url_tm_s',
        'datasize_readable_txt',
        'mii_support',
        'voice_chat_b',
        'download_play',
        'wishlist_email_square_image_url_s',
        'hits_i',
        'dlc_shown_b',
        'wishlist_email_banner460w_image_url_s',
        'wishlist_email_banner640w_image_url_s',
        'labo_b',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'eshop_europe_fs_id', 'fs_id');
    }

}