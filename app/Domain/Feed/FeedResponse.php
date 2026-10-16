<?php

namespace App\Domain\Feed;

/**
 * One HTTP response from a feed URL, kept whole so it can be classified and, if something went
 * wrong, recorded as evidence on the feed link's run history.
 */
class FeedResponse
{
    /**
     * How much of a body to keep as evidence. The bot check pages seen so far are 12-115KB;
     * the part that says what they are is near the top.
     */
    const EXCERPT_LENGTH = 16000;

    /**
     * @param int|null $statusCode Null when the body didn't come from a request (a body passed
     *                             straight to Loader::loadFromBody).
     * @param array $headers Lower-cased header name => first value.
     */
    public function __construct(
        public readonly ?int $statusCode,
        public readonly ?string $contentType,
        public readonly string $body,
        public readonly array $headers = []
    )
    {
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function bodyExcerpt(): string
    {
        return mb_substr($this->body, 0, self::EXCERPT_LENGTH);
    }
}
