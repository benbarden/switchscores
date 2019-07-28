<?php

namespace App\Services;

use App\MarioMakerLevel;

class MarioMakerLevelService
{
    public function create(
        $userId, $levelCode, $gameStyleId, $title, $description, $status
    )
    {
        $values = [
            'user_id' => $userId,
            'level_code' => $levelCode,
            'game_style_id' => $gameStyleId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
        ];
        return MarioMakerLevel::create($values);
    }

    public function edit(
        MarioMakerLevel $marioMakerLevel,
        $userId, $levelCode, $gameStyleId, $title, $description, $status
    )
    {
        $values = [
            'user_id' => $userId,
            'level_code' => $levelCode,
            'game_style_id' => $gameStyleId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
        ];

        $marioMakerLevel->fill($values);
        $marioMakerLevel->save();
    }

    public function markAsApproved(MarioMakerLevel $marioMakerLevel)
    {
        $marioMakerLevel->status = MarioMakerLevel::STATUS_APPROVED;
        $marioMakerLevel->save();
    }

    public function markAsRejected(MarioMakerLevel $marioMakerLevel)
    {
        $marioMakerLevel->status = MarioMakerLevel::STATUS_REJECTED;
        $marioMakerLevel->save();
    }

    // ********************************************************** //

    public function getGameStyleList()
    {
        $styles = [];
        $styles[] = ['id' => MarioMakerLevel::STYLE_SMB, 'desc' => MarioMakerLevel::STYLE_DESC_SMB];
        $styles[] = ['id' => MarioMakerLevel::STYLE_SMB3, 'desc' => MarioMakerLevel::STYLE_DESC_SMB3];
        $styles[] = ['id' => MarioMakerLevel::STYLE_SMW, 'desc' => MarioMakerLevel::STYLE_DESC_SMW];
        $styles[] = ['id' => MarioMakerLevel::STYLE_NSMB, 'desc' => MarioMakerLevel::STYLE_DESC_NSMB];
        $styles[] = ['id' => MarioMakerLevel::STYLE_SM3DW, 'desc' => MarioMakerLevel::STYLE_DESC_SM3DW];
        return $styles;
    }

    // ********************************************************** //

    public function find($id)
    {
        return MarioMakerLevel::find($id);
    }

    public function getAll()
    {
        return MarioMakerLevel::orderBy('created_at', 'desc')->get();
    }

    public function getApproved()
    {
        return MarioMakerLevel::
            where('status', MarioMakerLevel::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getPending()
    {
        return MarioMakerLevel::
            where('status', MarioMakerLevel::STATUS_PENDING)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getAllWithLimit($limit)
    {
        return MarioMakerLevel::
            orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByLevelCode($levelCode)
    {
        return MarioMakerLevel::where('level_code', $levelCode)->first();
    }

    public function getByUserId($userId)
    {
        return MarioMakerLevel::where('user_id', $userId)->get();
    }
}