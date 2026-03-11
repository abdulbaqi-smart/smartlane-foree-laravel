<?php

namespace Yourvendor\Foree\Services\DTO;

class ForeeResponse
{
    public function __construct(
        public int $responseCode,
        public array $responseData = [],
        public array $raw = [],
    ) {}

    public function ok(): bool
    {
        return $this->responseCode === 0;
    }
}
