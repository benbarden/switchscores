<?php

namespace App\Construction\GameChangeHistory;

use App\Game;
use App\GameChangeHistory;

class Builder
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var Game
     */
    private $gameOriginal;

    /**
     * @var GameChangeHistory
     */
    private $gameChangeHistory;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->game = new Game;
        $this->gameOriginal = new Game;
        $this->gameChangeHistory = new GameChangeHistory;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function setGameId($gameId): void
    {
        $this->gameChangeHistory->game_id = $gameId;
    }

    public function getGameOriginal(): Game
    {
        return $this->gameOriginal;
    }

    public function setGameOriginal(Game $game): void
    {
        $this->gameOriginal = $game;
    }

    public function getGameChangeHistory(): GameChangeHistory
    {
        return $this->gameChangeHistory;
    }

    public function setGameChangeHistory(GameChangeHistory $gameChangeHistory): void
    {
        $this->gameChangeHistory = $gameChangeHistory;
    }

    public function convertArrayToJson($data): string
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        return $data;
    }

    public function getArrayDifferences($orig, $new): array
    {
        $changed = [];
        $fieldsToIgnore = ['created_at', 'updated_at'];

        // 1. Handle removed fields and value changes
        foreach ($orig as $key => $value) {

            if (in_array($key, $fieldsToIgnore)) continue;

            if (array_key_exists($key, $new)) {

                // Key is in new array
                if ($new[$key] != $value) {

                    // Different value
                    $changed[$key] = $new[$key];

                }

            } else {

                // Key is not in new array, so it's different
                $changed[$key] = $value;

            }

        }

        // 2. Handle new fields
        foreach ($new as $key => $value) {

            if (in_array($key, $fieldsToIgnore)) continue;

            if (!array_key_exists($key, $orig)) {

                // Ignore if blank
                if (($value == '') || ($value == null)) continue;

                // Field added in new array
                $changed[$key] = $value;

            }

        }

        return $changed;
    }

    public function setTableName($tableName): Builder
    {
        $this->gameChangeHistory->affected_table_name = $tableName;
        return $this;
    }

    public function setTableNameGames(): Builder
    {
        $this->gameChangeHistory->affected_table_name = GameChangeHistory::TABLE_NAME_GAMES;
        return $this;
    }

    public function setSourceEshopEurope(): Builder
    {
        $this->gameChangeHistory->source = GameChangeHistory::SOURCE_ESHOP_EUROPE;
        return $this;
    }

    public function setSourceEshopUS(): Builder
    {
        $this->gameChangeHistory->source = GameChangeHistory::SOURCE_ESHOP_US;
        return $this;
    }

    public function setSourceWikipedia(): Builder
    {
        $this->gameChangeHistory->source = GameChangeHistory::SOURCE_WIKIPEDIA;
        return $this;
    }

    public function setSourceAdmin(): Builder
    {
        $this->gameChangeHistory->source = GameChangeHistory::SOURCE_ADMIN;
        return $this;
    }

    public function setSourceMember(): Builder
    {
        $this->gameChangeHistory->source = GameChangeHistory::SOURCE_MEMBER;
        return $this;
    }

    public function setChangeTypeInsert(): Builder
    {
        $this->gameChangeHistory->change_type = GameChangeHistory::CHANGE_TYPE_INSERT;
        return $this;
    }

    public function setChangeTypeUpdate(): Builder
    {
        $this->gameChangeHistory->change_type = GameChangeHistory::CHANGE_TYPE_UPDATE;
        return $this;
    }

    public function setChangeTypeDelete(): Builder
    {
        $this->gameChangeHistory->change_type = GameChangeHistory::CHANGE_TYPE_DELETE;
        return $this;
    }

    public function setUserId($userId): Builder
    {
        $this->gameChangeHistory->user_id = $userId;
        return $this;
    }

    public function setDataOld($data): Builder
    {
        if (is_array($data)) {
            $data = $this->convertArrayToJson($data);
        }
        $this->gameChangeHistory->data_old = $data;
        return $this;
    }

    public function setDataNew($data): Builder
    {
        if (is_array($data)) {
            $data = $this->convertArrayToJson($data);
        }
        $this->gameChangeHistory->data_new = $data;
        return $this;
    }

    public function setDataChanged($data): Builder
    {
        if (is_array($data)) {
            $data = $this->convertArrayToJson($data);
        }
        $this->gameChangeHistory->data_changed = $data;
        return $this;
    }

}