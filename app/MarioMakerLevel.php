<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarioMakerLevel extends Model
{
    const STYLE_SMB = 1;
    const STYLE_SMB3 = 3;
    const STYLE_SMW = 4;
    const STYLE_NSMB = 10;
    const STYLE_SM3DW = 20;

    const STYLE_DESC_SMB = 'Super Mario Bros';
    const STYLE_DESC_SMB3 = 'Super Mario Bros 3';
    const STYLE_DESC_SMW = 'Super Mario World';
    const STYLE_DESC_NSMB = 'New Super Mario Bros';
    const STYLE_DESC_SM3DW = 'Super Mario 3D World';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'level_code', 'game_style_id', 'title', 'description', 'status'
    ];

    /**
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * @var array
     */
    protected $casts = [
    ];

    public function getGameStyleDesc()
    {
        $gameStyle = '';

        switch ($this->game_style_id) {
            case self::STYLE_SMB:
                $gameStyle = self::STYLE_DESC_SMB;
                break;
            case self::STYLE_SMB3:
                $gameStyle = self::STYLE_DESC_SMB3;
                break;
            case self::STYLE_SMW:
                $gameStyle = self::STYLE_DESC_SMW;
                break;
            case self::STYLE_NSMB:
                $gameStyle = self::STYLE_DESC_NSMB;
                break;
            case self::STYLE_SM3DW:
                $gameStyle = self::STYLE_DESC_SM3DW;
                break;
        }

        return $gameStyle;
    }

    public function getStatusDesc()
    {
        $statusDesc = '';

        switch ($this->status) {
            case self::STATUS_PENDING:
                $statusDesc = 'Pending';
                break;
            case self::STATUS_APPROVED:
                $statusDesc = 'Approved';
                break;
            case self::STATUS_REJECTED:
                $statusDesc = 'Rejected';
        }

        return $statusDesc;
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
