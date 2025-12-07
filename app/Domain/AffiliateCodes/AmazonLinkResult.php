<?php

namespace App\Domain\AffiliateCodes;

class AmazonLinkResult
{
    public function __construct(
        public readonly ?string $urlUk,
        public readonly ?string $urlUs,
        public readonly string $usType // 'product' or 'search'
    ) {}
}