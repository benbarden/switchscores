<?php

namespace App\Domain\ViewBindings;

class Member extends Base
{
    public function generateMember($pageTitle)
    {
        $this->setPageTitle($pageTitle);
        $this->setTopTitleSuffix('Members');

        return $this->generate();
    }
}