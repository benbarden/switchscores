<?php

namespace App\Domain\Feed;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Fetches a feed's raw body over HTTP.
 *
 * Extracted from Loader so the probe and the importer send identical requests. A partner
 * whitelisting our user agent, or blocking it, must see the same thing from both, otherwise
 * a feed can probe clean and then fail on the nightly run.
 */
class FeedFetcher
{
    const USER_AGENT = 'switchscores/v2.0';

    const TIMEOUT_SECONDS = 30;

    /**
     * @param string $feedUrl
     * @return string The raw response body.
     * @throws \Exception when the feed cannot be retrieved.
     */
    public function fetch($feedUrl)
    {
        try {
            $client = new GuzzleClient(
                [
                    'headers' => [
                        'User-Agent' => self::USER_AGENT,
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Accept-Encoding' => 'gzip, deflate',
                    ],
                    'verify' => false,
                    'timeout' => self::TIMEOUT_SECONDS,
                ]
            );
            $response = $client->request('GET', $feedUrl);
        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Error: '.$e->getMessage());
        }

        try {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Status code: '.$statusCode.'Error: '.$e->getMessage());
        }

        if ($statusCode != 200) {
            throw new \Exception('Cannot load feed: '.$feedUrl);
        }

        return (string) $body;
    }
}
