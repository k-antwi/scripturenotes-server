<?php

namespace Nucleus\Scripture\Bible\Exceptions;

use RuntimeException;

class ProviderUnavailableException extends RuntimeException
{
    public function __construct(string $provider, string $reason = '', int $code = 502, ?\Throwable $previous = null)
    {
        $message = "Bible provider [{$provider}] is unavailable.";
        if ($reason !== '') {
            $message .= " Reason: {$reason}";
        }

        parent::__construct($message, $code, $previous);
    }
}
