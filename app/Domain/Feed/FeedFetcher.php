<?php

namespace App\Domain\Feed;

use App\Domain\Relay\EgressRelay;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;

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
 *
 * A feed can be fetched through the egress relay (a Cloudflare Worker) instead of directly,
 * for sites that refuse the server's datacentre IP. See EgressRelay and #174.
 */
class FeedFetcher
{
    const USER_AGENT = 'switchscores/v2.0';

    const TIMEOUT_SECONDS = 30;

    /**
     * @param EgressRelay|null $relay Defaults to the configured relay.
     * @param callable|null $handler A Guzzle handler, so tests can stand in for the network.
     */
    public function __construct(
        private ?EgressRelay $relay = null,
        private $handler = null
    )
    {
    }

    /**
     * Fetches the feed and checks the response is a feed.
     *
     * @param string $feedUrl
     * @param bool $viaRelay Fetch through the egress relay rather than directly.
     * @return string The raw response body.
     * @throws FeedLoadException when the feed cannot be retrieved or isn't a feed.
     */
    public function fetch($feedUrl, bool $viaRelay = false)
    {
        $response = $this->fetchResponse($feedUrl, $viaRelay);

        (new FeedResponseClassifier())->assertFeed($response);

        return $response->body;
    }

    /**
     * Fetches the feed and returns the response whatever its status or content, for callers
     * that want to classify or record it themselves.
     *
     * @throws FeedLoadException when no response came back at all, or the relay refused.
     */
    public function fetchResponse($feedUrl, bool $viaRelay = false): FeedResponse
    {
        $relay = null;
        $requestUrl = $feedUrl;
        $extraHeaders = [];

        if ($viaRelay) {
            $relay = $this->relay ?? EgressRelay::fromConfig();
            if (!$relay->isConfigured()) {
                throw new FeedLoadException(
                    'This feed is set to fetch via the relay, but RELAY_URL / RELAY_KEY are not configured',
                    FeedRunOutcome::RELAY_ERROR
                );
            }
            $requestUrl = $relay->relayUrl($feedUrl);
            $extraHeaders = $relay->headers();
        }

        try {
            $options = [
                'headers' => array_merge([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                ], $extraHeaders),
                'verify' => false,
                'timeout' => self::TIMEOUT_SECONDS,
                'http_errors' => false,
            ];
            if ($this->handler) {
                $options['handler'] = HandlerStack::create($this->handler);
            }
            $client = new GuzzleClient($options);
            $response = $client->request('GET', $requestUrl);
            $body = (string) $response->getBody();
        } catch (\Exception $e) {
            throw new FeedLoadException(
                ($viaRelay ? 'Could not connect to the relay: ' : 'Could not connect: ').self::firstLine($e->getMessage()),
                FeedRunOutcome::CONNECTION_ERROR,
                null,
                $e
            );
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = $values[0] ?? '';
        }

        $feedResponse = new FeedResponse(
            $response->getStatusCode(),
            $response->getHeaderLine('Content-Type') ?: null,
            $body,
            $headers
        );

        if ($relay) {
            $refusal = $relay->refusal($feedResponse->statusCode, $body);
            if ($refusal !== null) {
                throw new FeedLoadException(
                    'Relay error: '.$refusal,
                    FeedRunOutcome::RELAY_ERROR,
                    $feedResponse
                );
            }
        }

        return $feedResponse;
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
