<?php

namespace App\Domain\ViewBindings;

class MainSite extends Base
{
    public function generateMain($pageTitle)
    {
        $this->setPageTitle($pageTitle);
        $this->setTopTitleSuffix('Switch Scores');

        return $this->generate();
    }
}