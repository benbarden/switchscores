<?php

namespace App\Enums;

enum GameListType: string
{
    case RECENTLY_ADDED = 'recently-added';
    case RECENTLY_RELEASED = 'recently-released';
    case UPCOMING_GAMES = 'upcoming-games';
    case UPCOMING_ESHOP_CROSSCHECK = 'upcoming-eshop-crosscheck';

    // No category
    case NO_CATEGORY_EXCLUDING_LOW_QUALITY = 'no-category-excluding-low-quality';
    case NO_CATEGORY_ALL = 'no-category-all';
    case NO_CATEGORY_WITH_COLLECTION = 'no-category-with-collection';
    case NO_CATEGORY_WITH_REVIEWS = 'no-category-with-reviews';

    // Missing data
    case NO_EU_RELEASE_DATE = 'no-eu-release-date';
    case NO_ESHOP_PRICE = 'no-eshop-price';
    case NO_VIDEO_TYPE = 'no-video-type';
    case NO_AMAZON_UK_LINK = 'no-amazon-uk-link';
    case NO_AMAZON_US_LINK = 'no-amazon-us-link';
    case NO_NINTENDO_CO_UK_LINK = 'no-nintendo-co-uk-link';
    case BROKEN_NINTENDO_CO_UK_LINK = 'broken-nintendo-co-uk-link';

    // Format option
    case FORMAT_OPTION = 'format-option';

    // Dynamic lists
    case BY_CATEGORY = 'by-category';
    case BY_SERIES = 'by-series';
    case BY_TAG = 'by-tag';
    case BY_COLLECTION = 'by-collection';

}