<?php

namespace App\Construction\GameChangeHistory;


use App\Game;
use Illuminate\Http\Request;

class Director
{
    /**
     * @var Builder
     */
    private $builder;

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    public function setTableNameGames(): void
    {
        $this->builder->setTableNameGames();
    }

    public function setGameId(): void
    {
        $game = $this->builder->getGame();
        if ($game != null) {
            $this->builder->setGameId($this->builder->getGame()->id);
        }
    }

    public function setUserId($userId): void
    {
        $this->builder->setUserId($userId);
    }

    public function buildWikipediaInsert(): void
    {
        $this->setGameId();
        $this->builder->setSourceWikipedia();
        $this->builder->setChangeTypeInsert();
        $this->buildDataForInsert();
    }

    public function buildWikipediaUpdate(): void
    {
        $this->setGameId();
        $this->builder->setSourceWikipedia();
        $this->builder->setChangeTypeUpdate();
        $this->buildDataForUpdate();
    }

    public function buildEshopEuropeUpdate(): void
    {
        $this->setGameId();
        $this->builder->setSourceEshopEurope();
        $this->builder->setChangeTypeUpdate();
        $this->buildDataForUpdate();
    }

    public function buildAdminInsert(): void
    {
        $this->setGameId();
        $this->builder->setSourceAdmin();
        $this->builder->setChangeTypeInsert();
        $this->buildDataForInsert();
    }

    public function buildAdminUpdate(): void
    {
        $this->setGameId();
        $this->builder->setSourceAdmin();
        $this->builder->setChangeTypeUpdate();
        $this->buildDataForUpdate();
    }

    public function buildAdminDelete(): void
    {
        $this->setGameId();
        $this->builder->setSourceAdmin();
        $this->builder->setChangeTypeDelete();
        $this->buildDataForDelete();
    }

    public function buildDataForInsert(): bool
    {
        $game = $this->builder->getGame();
        if ($game == null) return false;

        $dataNew = $game->toArray();
        $this->builder->setDataOld(null);
        $this->builder->setDataNew($dataNew);
        $this->builder->setDataChanged(null);

        return true;
    }

    public function buildDataForUpdate(): bool
    {
        $game = $this->builder->getGame();
        $gameOriginal = $this->builder->getGameOriginal();
        if ($game == null) return false;

        $dataNew = $game->toArray();
        $dataOld = $gameOriginal->toArray();
        $dataChanged = $this->builder->getArrayDifferences($dataOld, $dataNew);
        $this->builder->setDataOld($dataOld);
        $this->builder->setDataNew($dataNew);
        $this->builder->setDataChanged($dataChanged);

        return true;
    }

    public function buildDataForDelete(): bool
    {
        $game = $this->builder->getGame();
        if ($game == null) return false;

        $dataOld = $game->toArray();
        $this->builder->setDataOld($dataOld);
        $this->builder->setDataNew(null);
        $this->builder->setDataChanged(null);

        return true;
    }
}