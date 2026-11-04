<?php

namespace App\Domain\AffiliateCodes;

/**
 * A Creators API failure. The message is safe to show staff: it never holds the credential or token.
 */
class CreatorsApiException extends \RuntimeException
{
}
