<?php

namespace App\Domain\ViewBindings;

class Staff extends Base
{
    public function generateStaff($pageTitle)
    {
        $this->setPageTitle($pageTitle);
        $this->setTopTitleSuffix('Staff');

        return $this->generate();
    }
}