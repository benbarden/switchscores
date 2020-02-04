<?php


namespace App\Services;

use App\PartnerOutreach;
use Illuminate\Support\Collection;

class PartnerOutreachService
{
    public function create(
        $partnerId, $newStatus, $contactMethod = null, $contactMessage = null, $internalNotes = null
    )
    {
        return PartnerOutreach::create([
            'partner_id' => $partnerId,
            'new_status' => $newStatus,
            'contact_method' => $contactMethod,
            'contact_message' => $contactMessage,
            'internal_notes' => $internalNotes,
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

    // ********************************************************** //

    /**
     * @param $id
     * @return PartnerOutreach
     */
    public function find($id)
    {
        return PartnerOutreach::find($id);
    }

    /**
     * @param $partnerId
     * @return Collection
     */
    public function getByPartnerId($partnerId)
    {
        return PartnerOutreach::where('partner_id', $partnerId)->orderBy('id', 'desc')->get();
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return PartnerOutreach::orderBy('id', 'desc')->get();
    }

    public function getStatusList()
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

    public function getContactMethodList()
    {
        $methodList = [];

        $methodList[] = ['title' => PartnerOutreach::METHOD_TWITTER_DM];
        $methodList[] = ['title' => PartnerOutreach::METHOD_TWITTER_TWEET];
        $methodList[] = ['title' => PartnerOutreach::METHOD_EMAIL];

        return $methodList;
    }
}