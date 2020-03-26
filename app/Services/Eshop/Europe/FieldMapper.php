<?php

namespace App\Services\Eshop\Europe;


class FieldMapper
{
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_JSON = 'json';

    /**
     * @var string
     */
    private $eshopField;

    /**
     * @var array
     */
    private $fieldMapping = [];

    public function __construct()
    {
        $this->fieldMapping['_version_'] = [
            'dbField' => 'version', 'type' => self::TYPE_STRING
        ];

        $this->fieldMapping['add_on_content_b'] = [
            'dbField' => 'add_on_content_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Add-on content'
        ];
        $this->fieldMapping['age_rating_sorting_i'] = [
            'dbField' => 'age_rating_sorting_i', 'type' => ''
        ];
        $this->fieldMapping['age_rating_type'] = [
            'dbField' => 'age_rating_type', 'type' => ''
        ];
        $this->fieldMapping['age_rating_value'] = [
            'dbField' => 'age_rating_value', 'type' => ''
        ];
        $this->fieldMapping['change_date'] = [
            'dbField' => 'change_date', 'type' => ''
        ];
        $this->fieldMapping['cloud_saves_b'] = [
            'dbField' => 'cloud_saves_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Cloud saves'
        ];
        $this->fieldMapping['club_nintendo'] = [
            'dbField' => 'club_nintendo', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Club Nintendo'
        ];
        $this->fieldMapping['compatible_controller'] = [
            'dbField' => 'compatible_controller', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['coop_play_b'] = [
            'dbField' => 'coop_play_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Co-op play'
        ];
        $this->fieldMapping['copyright_s'] = [
            'dbField' => 'copyright_s', 'type' => ''
        ];
        $this->fieldMapping['date_from'] = [
            'dbField' => 'date_from', 'type' => ''
        ];
        $this->fieldMapping['dates_released_dts'] = [
            'dbField' => 'dates_released_dts', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['datasize_readable_txt'] = [
            'dbField' => 'datasize_readable_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['demo_availability'] = [
            'dbField' => 'demo_availability', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Demo availability'
        ];
        $this->fieldMapping['deprioritise_b'] = [
            'dbField' => 'deprioritise_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Deprioritised'
        ];
        $this->fieldMapping['developer'] = [
            'dbField' => 'developer', 'type' => ''
        ];
        $this->fieldMapping['digital_version_b'] = [
            'dbField' => 'digital_version_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Digital version'
        ];
        $this->fieldMapping['dlc_shown_b'] = [
            'dbField' => 'dlc_shown_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'DLC shown'
        ];
        $this->fieldMapping['download_play'] = [
            'dbField' => 'download_play', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Download play'
        ];
        $this->fieldMapping['eshop_removed_b'] = [
            'dbField' => 'eshop_removed_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'eShop removed'
        ];
        $this->fieldMapping['excerpt'] = [
            'dbField' => 'excerpt', 'type' => ''
        ];
        $this->fieldMapping['fs_id'] = [
            'dbField' => 'fs_id', 'type' => ''
        ];
        $this->fieldMapping['game_categories_txt'] = [
            'dbField' => 'game_categories_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['game_category'] = [
            'dbField' => 'game_category', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['game_series_txt'] = [
            'dbField' => 'game_series_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['game_series_t'] = [
            'dbField' => 'game_series_t', 'type' => ''
        ];
        $this->fieldMapping['gift_finder_carousel_image_url_s'] = [
            'dbField' => 'gift_finder_carousel_image_url_s', 'type' => ''
        ];
        $this->fieldMapping['gift_finder_description_s'] = [
            'dbField' => 'gift_finder_description_s', 'type' => ''
        ];
        $this->fieldMapping['gift_finder_detail_page_image_url_s'] = [
            'dbField' => 'gift_finder_detail_page_image_url_s', 'type' => ''
        ];
        $this->fieldMapping['gift_finder_detail_page_store_link_s'] = [
            'dbField' => 'gift_finder_detail_page_store_link_s', 'type' => ''
        ];
        $this->fieldMapping['gift_finder_wishlist_image_url_s'] = [
            'dbField' => 'gift_finder_wishlist_image_url_s', 'type' => ''
        ];
        $this->fieldMapping['hd_rumble_b'] = [
            'dbField' => 'hd_rumble_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'HD rumble'
        ];
        $this->fieldMapping['hits_i'] = [
            'dbField' => 'hits_i', 'type' => ''
        ];
        $this->fieldMapping['image_url'] = [
            'dbField' => 'image_url', 'type' => ''
        ];
        $this->fieldMapping['image_url_h2x1_s'] = [
            'dbField' => 'image_url_h2x1_s', 'type' => ''
        ];
        $this->fieldMapping['image_url_sq_s'] = [
            'dbField' => 'image_url_sq_s', 'type' => ''
        ];
        $this->fieldMapping['image_url_tm_s'] = [
            'dbField' => 'image_url_tm_s', 'type' => ''
        ];
        $this->fieldMapping['indie_b'] = [
            'dbField' => 'indie_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Indie'
        ];
        $this->fieldMapping['internet'] = [
            'dbField' => 'internet', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Internet'
        ];
        $this->fieldMapping['ir_motion_camera_b'] = [
            'dbField' => 'ir_motion_camera_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'IR motion camera'
        ];
        $this->fieldMapping['labo_b'] = [
            'dbField' => 'labo_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Labo'
        ];
        $this->fieldMapping['language_availability'] = [
            'dbField' => 'language_availability', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['local_play'] = [
            'dbField' => 'local_play', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Local play'
        ];
        $this->fieldMapping['match_play_b'] = [
            'dbField' => 'match_play_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Match play'
        ];
        $this->fieldMapping['mii_support'] = [
            'dbField' => 'mii_support', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Mii support'
        ];
        $this->fieldMapping['multiplayer_mode'] = [
            'dbField' => 'multiplayer_mode', 'type' => ''
        ];
        $this->fieldMapping['near_field_comm_b'] = [
            'dbField' => 'near_field_comm_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Near-field communication (NFC)'
        ];
        $this->fieldMapping['nintendo_switch_online_exclusive_b'] = [
            'dbField' => 'nintendo_switch_online_exclusive_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Nintendo Switch Online exclusive'
        ];
        $this->fieldMapping['nsuid_txt'] = [
            'dbField' => 'nsuid_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['off_tv_play_b'] = [
            'dbField' => 'off_tv_play_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Off-TV play'
        ];
        $this->fieldMapping['originally_for_t'] = [
            'dbField' => 'originally_for_t', 'type' => ''
        ];
        $this->fieldMapping['paid_subscription_required_b'] = [
            'dbField' => 'paid_subscription_required_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Paid subscription required'
        ];
        $this->fieldMapping['pg_s'] = [
            'dbField' => 'pg_s', 'type' => ''
        ];
        $this->fieldMapping['physical_version_b'] = [
            'dbField' => 'physical_version_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Physical version'
        ];
        $this->fieldMapping['play_coins'] = [
            'dbField' => 'play_coins', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Play coins'
        ];
        $this->fieldMapping['play_mode_handheld_mode_b'] = [
            'dbField' => 'play_mode_handheld_mode_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Play mode handheld'
        ];
        $this->fieldMapping['play_mode_tabletop_mode_b'] = [
            'dbField' => 'play_mode_tabletop_mode_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Play mode tabletop'
        ];
        $this->fieldMapping['play_mode_tv_mode_b'] = [
            'dbField' => 'play_mode_tv_mode_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Play mode TV'
        ];
        $this->fieldMapping['playable_on_txt'] = [
            'dbField' => 'playable_on_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['players_from'] = [
            'dbField' => 'players_from', 'type' => ''
        ];
        $this->fieldMapping['players_to'] = [
            'dbField' => 'players_to', 'type' => ''
        ];
        $this->fieldMapping['popularity'] = [
            'dbField' => 'popularity', 'type' => self::TYPE_BOOLEAN
        ];
        $this->fieldMapping['pretty_agerating_s'] = [
            'dbField' => 'pretty_agerating_s', 'type' => ''
        ];
        $this->fieldMapping['pretty_date_s'] = [
            'dbField' => 'pretty_date_s', 'type' => ''
        ];
        $this->fieldMapping['pretty_game_categories_txt'] = [
            'dbField' => 'pretty_game_categories_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['price_discount_percentage_f'] = [
            'dbField' => 'price_discount_percentage_f', 'type' => ''
        ];
        $this->fieldMapping['price_discounted_f'] = [
            'dbField' => 'price_discounted_f', 'type' => ''
        ];
        $this->fieldMapping['price_has_discount_b'] = [
            'dbField' => 'price_has_discount_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Price has discount'
        ];
        $this->fieldMapping['price_lowest_f'] = [
            'dbField' => 'price_lowest_f', 'type' => ''
        ];
        $this->fieldMapping['price_regular_f'] = [
            'dbField' => 'price_regular_f', 'type' => ''
        ];
        $this->fieldMapping['price_sorting_f'] = [
            'dbField' => 'price_sorting_f', 'type' => ''
        ];
        $this->fieldMapping['priority'] = [
            'dbField' => 'priority', 'type' => ''
        ];
        $this->fieldMapping['product_code_ss'] = [
            'dbField' => 'product_code_ss', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['product_code_txt'] = [
            'dbField' => 'product_code_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['publisher'] = [
            'dbField' => 'publisher', 'type' => ''
        ];
        $this->fieldMapping['ranking_b'] = [
            'dbField' => 'ranking_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Ranking'
        ];
        $this->fieldMapping['reg_only_hidden'] = [
            'dbField' => 'reg_only_hidden', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Reg only hidden'
        ];
        $this->fieldMapping['switch_game_voucher_b'] = [
            'dbField' => 'switch_game_voucher_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Switch game voucher'
        ];
        $this->fieldMapping['sorting_title'] = [
            'dbField' => 'sorting_title', 'type' => ''
        ];
        $this->fieldMapping['system_names_txt'] = [
            'dbField' => 'system_names_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['system_type'] = [
            'dbField' => 'system_type', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['title'] = [
            'dbField' => 'title', 'type' => ''
        ];
        $this->fieldMapping['title_extras_txt'] = [
            'dbField' => 'title_extras_txt', 'type' => self::TYPE_JSON
        ];
        $this->fieldMapping['type'] = [
            'dbField' => 'type', 'type' => ''
        ];
        $this->fieldMapping['url'] = [
            'dbField' => 'url', 'type' => ''
        ];
        $this->fieldMapping['voice_chat_b'] = [
            'dbField' => 'voice_chat_b', 'type' => self::TYPE_BOOLEAN, 'reportTitle' => 'Voice chat'
        ];
        $this->fieldMapping['wishlist_email_banner460w_image_url_s'] = [
            'dbField' => 'wishlist_email_banner460w_image_url_s', 'type' => ''
        ];
        $this->fieldMapping['wishlist_email_banner640w_image_url_s'] = [
            'dbField' => 'wishlist_email_banner640w_image_url_s', 'type' => ''
        ];
        $this->fieldMapping['wishlist_email_square_image_url_s'] = [
            'dbField' => 'wishlist_email_square_image_url_s', 'type' => ''
        ];
    }

    /**
     * @param string $field
     * @return void
     */
    public function setField($field): void
    {
        $this->eshopField = $field;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->eshopField;
    }

    /**
     * @return bool
     */
    public function fieldExists(): bool
    {
        return array_key_exists($this->eshopField, $this->fieldMapping);
    }

    public function getDbFieldName()
    {
        if (!$this->fieldExists()) throw new \Exception('Field does not exist');

        $fieldData = $this->fieldMapping[$this->eshopField];
        return $fieldData['dbField'];
    }

    public function getDbFieldType()
    {
        if (!$this->fieldExists()) throw new \Exception('Field does not exist');

        $fieldData = $this->fieldMapping[$this->eshopField];
        $fieldType = $fieldData['type'];
        if (!$fieldType) $fieldType = self::TYPE_STRING;
        return $fieldType;
    }

    public function getReportTitle()
    {
        if (!$this->fieldExists()) throw new \Exception('Field does not exist');

        $fieldData = $this->fieldMapping[$this->eshopField];
        if (array_key_exists('reportTitle', $fieldData)) {
            $reportTitle = $fieldData['reportTitle'];
        } else {
            $reportTitle = $fieldData['dbField'];
        }
        return $reportTitle;
    }

    public function getBooleanReportList()
    {
        $reportList = [];
        foreach ($this->fieldMapping as $fieldMap) {
            if ($fieldMap['type'] == self::TYPE_BOOLEAN) {
                $reportList[] = $fieldMap;
            }
        }
        return $reportList;
    }

    public function isBoolean()
    {
        return $this->getDbFieldType() == self::TYPE_BOOLEAN;
    }

    public function isJson()
    {
        return $this->getDbFieldType() == self::TYPE_JSON;
    }

    public function isInteger()
    {
        return $this->getDbFieldType() == self::TYPE_INTEGER;
    }

    public function isString()
    {
        return $this->getDbFieldType() == self::TYPE_STRING;
    }
}