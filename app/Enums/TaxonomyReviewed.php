<?php

namespace App\Enums;

enum TaxonomyReviewed: int
{
    case NOT_REVIEWED = 0;
    case REVIEWED = 1;
    case DEPRECATED = 2;
}