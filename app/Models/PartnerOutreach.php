<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOutreach extends Model
{
    const STATUS_INITIATED = 10;
    const STATUS_AWAITING_REPLY = 20;
    const STATUS_ACTION_REQUIRED = 30;

    const STATUS_OUTREACH_SUCCESS = 101;
    const STATUS_OUTREACH_FAIL = 102;

    const STATUS_OUTREACH_STALE = 999;

    const METHOD_TWITTER_DM = 'Twitter (DM)';
    const METHOD_TWITTER_TWEET = 'Twitter (tweet)';
    const METHOD_EMAIL = 'Email';

    /**
     * @var string
     */
    protected $table = 'partner_outreach';

    /**
     * @var array
     */
    protected $fillable = [
        'partner_id', 'new_status', 'contact_method', 'contact_message', 'internal_notes'
    ];

    public function gamesCompany()
    {
        return $this->hasOne('App\Models\GamesCompany', 'id', 'partner_id');
    }

    public function getStatusDesc()
    {
        $statusDesc = '';

        switch ($this->new_status) {
            case self::STATUS_INITIATED:
                $statusDesc = 'Initiated';
                break;
            case self::STATUS_AWAITING_REPLY:
                $statusDesc = 'Awaiting reply';
                break;
            case self::STATUS_ACTION_REQUIRED:
                $statusDesc = 'Action required';
                break;
            case self::STATUS_OUTREACH_SUCCESS:
                $statusDesc = 'Outreach success';
                break;
            case self::STATUS_OUTREACH_FAIL:
                $statusDesc = 'Outreach fail';
                break;
            case self::STATUS_OUTREACH_STALE:
                $statusDesc = 'Outreach stale';
                break;
        }

        return $statusDesc;
    }

    public function isStatusInitiated()
    {
        return $this->new_status == self::STATUS_INITIATED;
    }

    public function isStatusAwaitingReply()
    {
        return $this->new_status == self::STATUS_AWAITING_REPLY;
    }

    public function isStatusActionRequired()
    {
        return $this->new_status == self::STATUS_ACTION_REQUIRED;
    }

    public function isStatusOutreachSuccess()
    {
        return $this->new_status == self::STATUS_OUTREACH_SUCCESS;
    }

    public function isStatusOutreachFail()
    {
        return $this->new_status == self::STATUS_OUTREACH_FAIL;
    }

    public function isStatusOutreachStale()
    {
        return $this->new_status == self::STATUS_OUTREACH_STALE;
    }
}
