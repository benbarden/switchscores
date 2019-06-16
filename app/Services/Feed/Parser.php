<?php

namespace App\Services\Feed;

use App\Partner;
use App\Services\Feed\TitleParser;


/**
 * A wrapper class for feed parsing.
 * @package App\Services\Feed
 */
class Parser
{
    /**
     * @var integer
     */
    private $siteId;

    /**
     * @var TitleParser
     */
    private $titleParser;

    /**
     * @param TitleParser $titleParser
     */
    public function __construct(TitleParser $titleParser)
    {
        $this->titleParser = $titleParser;
    }

    /**
     * @return \App\Services\Feed\TitleParser
     */
    public function getTitleParser()
    {
        return $this->titleParser;
    }

    /**
     * @param integer $siteId
     * @return void
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return integer
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * Baked-in rules for each site, so we can see which rules are needed where.
     * @return void
     */
    public function parseBySiteRules()
    {
        switch ($this->siteId) {
            case Partner::SITE_CUBED3:
                $this->titleParser->stripPlatformText();
                $this->titleParser->cleanupText();
                break;
            case Partner::SITE_DESTRUCTOID:
                // No feed URL yet
                break;
            case Partner::SITE_DIGITALLY_DOWNLOADED:
                // No feed URL yet
                break;
            case Partner::SITE_GAMESPEW:
                // Titles too inconsistent; will have to match these manually.
                break;
            case Partner::SITE_GOD_IS_A_GEEK:
                // Titles too inconsistent; will have to match these manually.
                break;
            case Partner::SITE_NINTENDO_INSIDER:
                $this->titleParser->stripReviewText();
                $this->titleParser->cleanupText();
                break;
            case Partner::SITE_NINTENDO_WORLD_REPORT:
                // No feed URL yet
                break;
            case Partner::SITE_SWITCH_PLAYER:
                $this->titleParser->stripReviewText();
                $this->titleParser->cleanupText();
                break;
            case Partner::SITE_VIDEO_CHUMS:
                $this->titleParser->stripReviewText();
                $this->titleParser->cleanupText();
                break;
            case Partner::SITE_THE_NEW_ODYSSEY:
                // Titles too inconsistent; will have to match these manually.
                break;
            case Partner::SITE_WOS:
                // N/A
                break;
            case Partner::SITE_MIKETENDO64:
            case Partner::SITE_NINDIE_SPOTLIGHT:
            case Partner::SITE_NINTENDO_LIFE:
            case Partner::SITE_PURE_NINTENDO:
            case Partner::SITE_THE_SWITCH_EFFECT:
            default:
                // Default settings that work for most sites
                $this->titleParser->stripPlatformText();
                $this->titleParser->stripReviewText();
                $this->titleParser->cleanupText();
                break;
        }
    }


}