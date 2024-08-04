<?php

namespace App\Domain\Scraper;

use App\Domain\Scraper\Base as BaseScraper;

class Score extends BaseScraper
{
    // Works for God is a Geek
    public function divItemPropRatingValueWithChildren()
    {
        $value = $this->domCrawler->filterXPath(
            '//div[@itemprop="ratingValue"]')->children()->first()->innerText();
        return $value;
    }

    // Slightly different format used by Pure Nintendo
    public function spanItemPropRatingValueNoChildren()
    {
        $value = $this->domCrawler->filterXPath(
            '//span[@itemprop="ratingValue"]')->innerText();
        return $value;
    }

    // Unstructured format used by Nintenpedia
    public function customNintenpedia()
    {
        $value = $this->domCrawler->filterXPath(
            '//div[@class="entry-content"]')->children()->last()->children()->first()->innerText();
        $value = str_replace('Rating: ', '', $value);
        $valueArray = explode('/', $value);
        if (count($valueArray) > 0) {
            return $valueArray[0];
        } else {
            return null;
        }
    }

    // Unstructured format used by Hey Poor Player
    public function customHeyPoorPlayer()
    {
        $value = $this->domCrawler->filterXPath(
            '//div[@class="post-entry"]/p/strong/span/span/span')->innerText();
        $value = str_replace('Final Verdict: ', '', $value);
        $valueArray = explode('/', $value);
        if (count($valueArray) > 0) {
            return $valueArray[0];
        } else {
            return null;
        }
    }

    // Unstructured format used by Switchaboo
    public function customSwitchaboo()
    {
        $value = $this->domCrawler->filterXPath(
            '//section[@class="gh-content gh-canvas"]/h2')->innerText();
        $value = str_replace('Final Score: ', '', $value);
        $valueArray = explode('/', $value);
        if (count($valueArray) > 0) {
            return $valueArray[0];
        } else {
            return null;
        }
    }

}