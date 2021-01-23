<?php


namespace App\Domain\FeaturedGame;


use App\FeaturedGame;

class Repository
{
    public function createFromUserSubmission($userId, $gameId, $featuredType)
    {
        FeaturedGame::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'featured_type' => $featuredType,
            'status' => FeaturedGame::STATUS_PENDING,
        ]);
    }

    public function create($userId, $gameId, $featuredDate, $featuredType, $status)
    {
        FeaturedGame::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'featured_date' => $featuredDate,
            'featured_type' => $featuredType,
            'status' => $status,
        ]);
    }

    public function edit(FeaturedGame $featuredGame, $gameId, $featuredDate, $featuredType, $status)
    {
        $values = [
            'game_id' => $gameId,
            'featured_date' => $featuredDate,
            'featured_type' => $featuredType,
            'status' => $status,
        ];

        $featuredGame->fill($values)->save();
    }

    public function acceptItem(FeaturedGame $featuredGame)
    {
        $featuredGame->status = FeaturedGame::STATUS_ACCEPTED;
        $featuredGame->save();
    }

    public function rejectItem(FeaturedGame $featuredGame)
    {
        $featuredGame->status = FeaturedGame::STATUS_REJECTED;
        $featuredGame->save();
    }

    public function archiveItem(FeaturedGame $featuredGame)
    {
        $featuredGame->status = FeaturedGame::STATUS_ARCHIVED;
        $featuredGame->save();
    }

    public function find($id)
    {
        return FeaturedGame::find($id);
    }

    public function getAll()
    {
        return FeaturedGame::orderBy('id', 'desc')->get();
    }

    public function getAllGameIds()
    {
        return FeaturedGame::orderBy('id', 'desc')->pluck('game_id');
    }

    public function getActiveByDate($date)
    {
        return FeaturedGame::where('featured_date', $date)->where('status', FeaturedGame::STATUS_ACCEPTED)->orderBy('id', 'desc')->first();
    }

    public function getActiveByDateOrRandom($date)
    {
        $todaysGame = $this->getActiveByDate($date);
        if ($todaysGame) {
            return $todaysGame;
        } else {
            $randomGame = FeaturedGame::where('status', FeaturedGame::STATUS_ACCEPTED)->inRandomOrder()->first();
            return $randomGame;
        }
    }

    public function countPending()
    {
        return FeaturedGame::where('status', FeaturedGame::STATUS_PENDING)->count();
    }
}