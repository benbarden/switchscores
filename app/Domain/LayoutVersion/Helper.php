<?php

namespace App\Domain\LayoutVersion;

use App\Enums\LayoutVersion;

class Helper
{
    public function buildList()
    {
        $values = [];
        $values[] = ['id' => LayoutVersion::LAYOUT_V1->value, 'name' => LayoutVersion::LAYOUT_V1->value];
        $values[] = ['id' => LayoutVersion::LAYOUT_V2->value, 'name' => LayoutVersion::LAYOUT_V2->value];
        return $values;
    }
}