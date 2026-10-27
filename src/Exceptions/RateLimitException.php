<?php

namespace Drakakisgeo\Billit\Exceptions;

use stdClass;
use Throwable;

/**
 * The rate limit of 60 requests per minute was exceeded (429).
 */
class RateLimitException extends BillitException
{
    public function __construct(
        string $message = '',
        ?int $statusCode = null,
        ?stdClass $responseBody = null,
        ?Throwable $previous = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
    }

    /**
     * Seconds to wait before retrying, when the API sent a Retry-After header.
     */
    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
