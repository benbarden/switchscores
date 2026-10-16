<?php

namespace App\Domain\Feed;

/**
 * A feed couldn't be loaded, with a one-line reason and, where there was one, the response.
 *
 * The message is what reaches the cron log and the Feed Links table, so it never contains the
 * response body. The body travels separately on $response, for the run history to keep.
 */
class FeedLoadException extends \Exception
{
    public function __construct(
        string $message,
        public readonly string $outcome,
        public readonly ?FeedResponse $response = null,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }
}
