<?php

namespace App\Domain\InviteCodeDenyList;

use App\Models\InviteCodeDenyList;

class Repository
{
    public function isDomainInDenyList($domain)
    {
        return InviteCodeDenyList::where('deny_item', $domain)->where('deny_type', InviteCodeDenyList::TYPE_DOMAIN)->exists();
    }
}