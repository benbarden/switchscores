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
    // Also used by PS3Blog.net
    public function spanItemPropRatingValueNoChildren()
    {
        $value = $this->domCrawler->filterXPath(
            '//span[@itemprop="ratingValue"]')->innerText();
        return $value;
    }

    // Unstructured format used by Nintenpedia
    public function customNintenpedia()
    {
        /**
         * This site has either introduced a new format, or they're inconsistent with how they write their ratings.
         * Previously, they used a simple format like this:
         * Rating: 7/10.
         *
         * On 21/09/24 I spotted that their format had changed to this:
         * (name of game) gets a 7/10.
         *
         * Two reviews from the same reviewer use this format, so perhaps it's one person using their own style.
         * But in the examples I checked, there's sometimes a random Unicode character appearing:
         * (name of game) gets a\u{A0}7/10.
         *
         * The best thing I can do is add support for multiple separators, check if they exist, and explode to
         * an array based on this.
         *
         * It would be a lot simpler if every site used the itemprop="ratingValue" format :-[
         */

        $possibleSeparators = [
            "Rating: ",
            "\u{A0}",
            " gets a ",
        ];

        $value = $this->domCrawler->filterXPath(
            '//div[@class="entry-content"]')->children()->last()->children()->first()->innerText();

        $useSeparator = null;
        foreach ($possibleSeparators as $separator) {
            if (strpos($value, $separator) !== false) {
                $useSeparator = $separator;
                break;
            }
        }
        if (is_null($useSeparator)) return null;

        $valueArray = explode($useSeparator, $value);

        if (count($valueArray) == 0) return null;

        $scoreToSplit = $valueArray[1];
        $finalScore = explode('/', $scoreToSplit);
        if (count($finalScore) > 0) {
            return $finalScore[0];
        } else {
            return null;
        }
    }

    // Unstructured format used by Hey Poor Player
    public function customHeyPoorPlayer()
    {
        // Does not work, so return null
        return null;

        $value = $this->domCrawler->filterXPath(
            '//div[@class="post-entry"]/h2/strong/span/span/span')->innerText();
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


    // Format used by PS4BlogNet
    public function customPS4BlogNet()
    {
        $value = $this->domCrawler->filterXPath('//div[@class="penci-review-score-num"]')->innerText();
        return $value;
    }
}