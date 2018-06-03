<?php


namespace App\Services;

use App\FeedItemGame;


class FeedItemGameService
{
    public function edit(
        FeedItemGame $feedItemGame, $gameId, $statusCode
    )
    {
        $feedItemGame->game_id = $gameId;
        $feedItemGame->status_code = $statusCode;
        $feedItemGame->status_desc = $this->getStatusDesc($statusCode);
        $feedItemGame->save();
    }

    public function getStatusDesc($statusCode)
    {
        $statusDesc = null;

        switch ($statusCode) {
            case FeedItemGame::STATUS_PENDING:
                $statusDesc = 'Pending';
                break;
            case FeedItemGame::STATUS_OK_TO_UPDATE:
                $statusDesc = 'OK to update';
                break;
            case FeedItemGame::STATUS_COMPLETE:
                $statusDesc = 'Complete';
                break;
            case FeedItemGame::STATUS_NO_UPDATE_NEEDED:
                $statusDesc = 'No update needed';
                break;
            case FeedItemGame::STATUS_SKIPPED_BY_USER:
                $statusDesc = 'Skipped by user';
                break;
            case FeedItemGame::STATUS_SKIPPED_BY_GAME_RULES:
                $statusDesc = 'Skipped by game rules';
                break;
            default:
                throw new \Exception('Unrecognised status code for FeedItemGame: ' . $statusCode);
                break;
        }

        return $statusDesc;
    }

    public function find($id)
    {
        return FeedItemGame::find($id);
    }

    public function getAll()
    {
        $list = FeedItemGame::
            orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getPending()
    {
        $list = FeedItemGame::
            where('status_code', FeedItemGame::STATUS_PENDING)
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getPendingWithGameId()
    {
        $list = FeedItemGame::
            where('status_code', FeedItemGame::STATUS_PENDING)
            ->whereNotNull('game_id')
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getPendingNoGameId()
    {
        $list = FeedItemGame::
            where('status_code', FeedItemGame::STATUS_PENDING)
            ->whereNull('game_id')
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getComplete()
    {
        $list = FeedItemGame::
            whereIn('status_code', [
                FeedItemGame::STATUS_COMPLETE,
            ])
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getForProcessing()
    {
        $list = FeedItemGame::
            where('status_code', FeedItemGame::STATUS_OK_TO_UPDATE)
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getInactive()
    {
        $list = FeedItemGame::
            whereNotIn('status_code', [
                FeedItemGame::STATUS_PENDING,
                FeedItemGame::STATUS_OK_TO_UPDATE,
                FeedItemGame::STATUS_COMPLETE,
            ])
            ->orderBy('id', 'asc')
            ->get();
        return $list;
    }

    public function getLastEntryByGameId($gameId)
    {
        $feedItem = FeedItemGame::
            where('game_id', $gameId)
            ->whereNotIn('status_code', [
                FeedItemGame::STATUS_NO_UPDATE_NEEDED,
                FeedItemGame::STATUS_SKIPPED_BY_USER,
                FeedItemGame::STATUS_SKIPPED_BY_GAME_RULES,
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        if ($feedItem) {
            return $feedItem->first();
        } else {
            return null;
        }
    }

    public function getActiveByTitle($title)
    {
        $feedItem = FeedItemGame::
            where('item_title', $title)
            ->whereIn('status_code', [
                FeedItemGame::STATUS_PENDING,
                FeedItemGame::STATUS_OK_TO_UPDATE,
            ])
            ->orderBy('created_at', 'asc')
            ->get();
        if ($feedItem) {
            return $feedItem->first();
        } else {
            return null;
        }
    }

    public function getActiveByGameId($gameId)
    {
        $feedItem = FeedItemGame::
            where('game_id', $gameId)
            ->whereIn('status_code', [
                FeedItemGame::STATUS_PENDING,
                FeedItemGame::STATUS_OK_TO_UPDATE,
            ])
            ->orderBy('created_at', 'asc')
            ->get();
        if ($feedItem) {
            return $feedItem->first();
        } else {
            return null;
        }
    }
}