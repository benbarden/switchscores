<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewQuickRating extends Model
{
    const STATUS_GREAT = 1;
    const STATUS_GOOD = 2;
    const STATUS_OK = 3;
    const STATUS_AVERAGE = 4;
    const STATUS_POOR = 5;

    /**
     * @var string
     */
    protected $table = 'review_quick_rating';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
    ];
}
