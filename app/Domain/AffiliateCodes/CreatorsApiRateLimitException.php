<?php

namespace App\Domain\AffiliateCodes;

/**
 * Amazon said too many requests (HTTP 429). Worth waiting and trying again, unlike other failures.
 */
class CreatorsApiRateLimitException extends CreatorsApiException
{
}
