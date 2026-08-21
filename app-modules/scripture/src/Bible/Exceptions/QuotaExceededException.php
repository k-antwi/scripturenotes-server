<?php

namespace Nucleus\Scripture\Bible\Exceptions;

use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    public function __construct(string $provider, int $code = 429, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Bible provider [{$provider}] has exceeded its quota.",
            $code,
            $previous
        );
    }
}
