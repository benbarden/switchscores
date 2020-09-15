<?php


namespace App\Services\GamesCollection;


class PlayStatus
{
    private $id;
    private $desc;
    private $icon;
    private $iconColor;

    const PLAY_STATUS_NOT_STARTED = 'not-started';
    const PLAY_STATUS_NOW_PLAYING = 'now-playing';
    const PLAY_STATUS_PAUSED = 'paused';
    const PLAY_STATUS_ABANDONED = 'abandoned';
    const PLAY_STATUS_COMPLETED = 'completed';
    const PLAY_STATUS_REPLAYING = 'replaying';
    const PLAY_STATUS_ENDLESS = 'endless';

    public function getId()
    {
        return $this->id;
    }

    public function getDesc()
    {
        return $this->desc;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function getIconColor()
    {
        return $this->iconColor;
    }

    public function generateById($statusId)
    {
        switch ($statusId) {
            case self::PLAY_STATUS_NOT_STARTED:
                $playStatusItem = $this->generateNotStarted();
                break;
            case self::PLAY_STATUS_NOW_PLAYING:
                $playStatusItem = $this->generateNowPlaying();
                break;
            case self::PLAY_STATUS_PAUSED:
                $playStatusItem = $this->generatePaused();
                break;
            case self::PLAY_STATUS_ABANDONED:
                $playStatusItem = $this->generateAbandoned();
                break;
            case self::PLAY_STATUS_COMPLETED:
                $playStatusItem = $this->generateCompleted();
                break;
        }

        return $playStatusItem;
    }

    public function generateNotStarted()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_NOT_STARTED;
        $playStatus->desc = 'Not started';
        $playStatus->icon = 'stop-circle';
        $playStatus->iconColor = '#cc0000';

        return $playStatus;
    }

    public function generateNowPlaying()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_NOW_PLAYING;
        $playStatus->desc = 'Now playing';
        $playStatus->icon = 'play-circle';
        $playStatus->iconColor = '#990099';

        return $playStatus;
    }

    public function generatePaused()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_PAUSED;
        $playStatus->desc = 'Paused - will return';
        $playStatus->icon = 'pause-circle';
        $playStatus->iconColor = '#FF8C00';

        return $playStatus;
    }

    public function generateAbandoned()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_ABANDONED;
        $playStatus->desc = 'Abandoned';
        $playStatus->icon = 'times-circle';
        $playStatus->iconColor = '#999999';

        return $playStatus;
    }

    public function generateCompleted()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_COMPLETED;
        $playStatus->desc = 'Completed';
        $playStatus->icon = 'check-circle';
        $playStatus->iconColor = '#009900';

        return $playStatus;
    }

    public function generateReplaying()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_REPLAYING;
        $playStatus->desc = 'Replaying';
        $playStatus->icon = 'recycle';
        $playStatus->iconColor = '#1f75fe';

        return $playStatus;
    }

    public function generateEndless()
    {
        $playStatus = new self;
        $playStatus->id = self::PLAY_STATUS_ENDLESS;
        $playStatus->desc = 'Endless';
        $playStatus->icon = 'infinity';
        $playStatus->iconColor = '#00d3e6';

        return $playStatus;
    }

    public function generateAll()
    {
        $statusList = [];
        $statusList[] = $this->generateNowPlaying();
        $statusList[] = $this->generatePaused();
        $statusList[] = $this->generateNotStarted();
        $statusList[] = $this->generateAbandoned();
        $statusList[] = $this->generateCompleted();
        $statusList[] = $this->generateReplaying();
        $statusList[] = $this->generateEndless();
        return $statusList;
    }
}