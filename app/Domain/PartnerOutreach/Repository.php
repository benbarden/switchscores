<?php

namespace App\Domain\PartnerOutreach;

use Illuminate\Support\Collection;
use App\Models\PartnerOutreach;

class Repository
{
    public function create(
        $partnerId, $newStatus, $contactMethod = null, $contactMessage = null, $internalNotes = null, $inviteCodeId = null
    )
    {
        return PartnerOutreach::create([
            'partner_id' => $partnerId,
            'new_status' => $newStatus,
            'contact_method' => $contactMethod,
            'contact_message' => $contactMessage,
            'internal_notes' => $internalNotes,
            'invite_code_id' => $inviteCodeId,
        ]);
    }

    public function edit(
        PartnerOutreach $partnerOutreach, $newStatus, $contactMethod = null, $contactMessage = null, $internalNotes = null
    )
    {
        $values = [
            'new_status' => $newStatus,
            'contact_method' => $contactMethod,
            'contact_message' => $contactMessage,
            'internal_notes' => $internalNotes,
        ];

        $partnerOutreach->fill($values)->save();
    }

    public function delete($id)
    {
        PartnerOutreach::where('id', $id)->delete();
    }

    // Quick updates
    public function setStatusSuccess(PartnerOutreach $partnerOutreach)
    {
        $partnerOutreach->new_status = PartnerOutreach::STATUS_OUTREACH_SUCCESS;
        $partnerOutreach->save();
    }

    /**
     * @param $id
     * @return \App\Models\PartnerOutreach
     */
    public function find($id)
    {
        return PartnerOutreach::find($id);
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return PartnerOutreach::orderBy('id', 'desc')->get();
    }

    /**
     * @param $partnerId
     * @return Collection
     */
    public function byPartnerId($partnerId)
    {
        return PartnerOutreach::where('partner_id', $partnerId)->orderBy('id', 'desc')->get();
    }

    public function statusList()
    {
        $statusList = [];

        $statusList[] = ['id' => PartnerOutreach::STATUS_INITIATED, 'title' => 'Initiated'];
        $statusList[] = ['id' => PartnerOutreach::STATUS_AWAITING_REPLY, 'title' => 'Awaiting reply'];
        $statusList[] = ['id' => PartnerOutreach::STATUS_ACTION_REQUIRED, 'title' => 'Action required'];

        $statusList[] = ['id' => PartnerOutreach::STATUS_OUTREACH_SUCCESS, 'title' => 'Outreach success'];
        $statusList[] = ['id' => PartnerOutreach::STATUS_OUTREACH_FAIL, 'title' => 'Outreach fail'];

        $statusList[] = ['id' => PartnerOutreach::STATUS_OUTREACH_STALE, 'title' => 'Outreach stale'];

        return $statusList;
    }

    public function contactMethodList()
    {
        $methodList = [];

        $methodList[] = ['title' => PartnerOutreach::METHOD_TWITTER_DM];
        $methodList[] = ['title' => PartnerOutreach::METHOD_TWITTER_TWEET];
        $methodList[] = ['title' => PartnerOutreach::METHOD_EMAIL];
        $methodList[] = ['title' => PartnerOutreach::METHOD_THREADS_POST];
        $methodList[] = ['title' => PartnerOutreach::METHOD_BLUESKY_POST];

        return $methodList;
    }
}