<?php

namespace App\Domain\Feed;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Fetches a feed's raw body over HTTP.
 *
 * Extracted from Loader so the probe and the importer send identical requests. A partner
 * whitelisting our user agent, or blocking it, must see the same thing from both, otherwise
 * a feed can probe clean and then fail on the nightly run.
 *
 * Every failure is a FeedLoadException with a one-line message. HTTP errors are not left to
 * Guzzle (http_errors off): its exception message includes part of the response body, which
 * is how bot check pages ended up in the cron log and the Feed Links table.
 */
class FeedFetcher
{
    const USER_AGENT = 'switchscores/v2.0';

    const TIMEOUT_SECONDS = 30;

    /**
     * Fetches the feed and checks the response is a feed.
     *
     * @param string $feedUrl
     * @return string The raw response body.
     * @throws FeedLoadException when the feed cannot be retrieved or isn't a feed.
     */
    public function fetch($feedUrl)
    {
        $response = $this->fetchResponse($feedUrl);

        (new FeedResponseClassifier())->assertFeed($response);

        return $response->body;
    }

    /**
     * Fetches the feed and returns the response whatever its status or content, for callers
     * that want to classify or record it themselves.
     *
     * @throws FeedLoadException only when no response came back at all.
     */
    public function fetchResponse($feedUrl): FeedResponse
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
                    'http_errors' => false,
                ]
            );
            $response = $client->request('GET', $feedUrl);
            $body = (string) $response->getBody();
        } catch (\Exception $e) {
            throw new FeedLoadException(
                'Could not connect: '.self::firstLine($e->getMessage()),
                FeedRunOutcome::CONNECTION_ERROR,
                null,
                $e
            );
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = $values[0] ?? '';
        }

        return new FeedResponse(
            $response->getStatusCode(),
            $response->getHeaderLine('Content-Type') ?: null,
            $body,
            $headers
        );
    }

    /**
     * First line of an error message, capped - enough to say what went wrong.
     */
    public static function firstLine(string $message, int $max = 200): string
    {
        $line = trim(strtok($message, "\r\n") ?: $message);
        return mb_strimwidth($line, 0, $max, '...');
    }
}
