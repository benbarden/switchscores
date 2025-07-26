<?php

namespace App\Enums;

enum CacheDuration: int
{
    case FIVE_MINUTES = 300;
    case ONE_HOUR = 3600;
    case SIX_HOURS = 21600;
    case TWELVE_HOURS = 43200;
    case ONE_DAY = 86400;
    case THREE_DAYS = 259200;
}