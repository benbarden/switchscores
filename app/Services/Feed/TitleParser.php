<?php

namespace App\Services\Feed;


class TitleParser
{
    /**
     * @var string
     */
    private $title;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return void
     */
    public function stripReviewText()
    {
        $this->title = str_replace('[Review]', '', $this->title);
        $this->title = str_replace('Mini-Review: ', '', $this->title);
        $this->title = str_replace('Review: ', '', $this->title);
        $this->title = str_replace('Review', '', $this->title);
    }

    /**
     * @return void
     */
    public function stripPlatformText()
    {
        $this->title = str_replace('(Switch eShop)', '', $this->title);
        $this->title = str_replace('(Nintendo Switch)', '', $this->title);
        $this->title = str_replace('(Switch)', '', $this->title);
        $this->title = str_replace('[Nintendo Switch eShop]', '', $this->title);
    }

    /**
     * @return void
     */
    public function cleanupText()
    {
        $this->title = trim($this->title);
        $this->title = str_replace('  ', ' ', $this->title);
    }

}