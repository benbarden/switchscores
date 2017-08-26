<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityFeed extends Model
{
    const TYPE_NEW_GAME = 1;
    const TYPE_NEW_CHART = 2;
    const TYPE_NEW_REVIEW = 3;

    /**
     * @var string
     */
    protected $table = 'activity_feed';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'activity_type', 'message', 'properties',
    ];

    public function isTypeNewGame()
    {
        return $this->activity_type == self::TYPE_NEW_GAME;
    }

    public function isTypeNewChart()
    {
        return $this->activity_type == self::TYPE_NEW_CHART;
    }

    public function isTypeNewReview()
    {
        return $this->activity_type == self::TYPE_NEW_REVIEW;
    }

    public function getMessageWithValues()
    {
        if ($this->isTypeNewGame()) {
            return $this->getMessageForNewGame();
        } elseif ($this->isTypeNewChart()) {
            return $this->getMessageForNewChart();
        } elseif ($this->isTypeNewReview()) {
            return $this->getMessageForNewReview();
        }
    }

    public function getMessageForNewGame()
    {
        $message = 'Added a new game: %s.';
        $feedProperties = json_decode($this->properties, true);

        $gameId = $feedProperties['game_id'];
        $serviceGame = resolve('Services\GameService');
        $game = $serviceGame->find($gameId);

        $gameTitle = $game->title;
        $gameLink = route('game.show', ['game_id' => $game->id, 'title' => $game->link_title]);
        $gameHtml = '<a href="'.$gameLink.'">'.$gameTitle.'</a>';

        return sprintf($message, $gameHtml);
    }

    public function getMessageForNewChart()
    {

    }

    public function getMessageForNewReview()
    {
        $message = 'Added a new review to %s. Source: %s; Rating: %s. '.
            '%s is now rated %s.';
        $feedProperties = json_decode($this->properties, true);

        $reviewId = $feedProperties['review_id'];
        $serviceReviewLink = resolve('Services\ReviewLinkService');
        $reviewLink = $serviceReviewLink->find($reviewId);

        $game = $reviewLink->game;

        $gameTitle = $game->title;
        $gameLink = route('game.show', ['game_id' => $game->id, 'title' => $game->link_title]);
        $gameHtml = '<a href="'.$gameLink.'">'.$gameTitle.'</a>';

        $reviewRating = $reviewLink->rating_normalised;
        $reviewSite = $reviewLink->site->name;

        $gameRating = $game->rating_avg;

        return sprintf($message, $gameHtml, $reviewSite, $reviewRating, $gameTitle, $gameRating);
    }
}
