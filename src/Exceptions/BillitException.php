<?php

namespace Drakakisgeo\Billit\Exceptions;

use RuntimeException;
use stdClass;
use Throwable;

/**
 * Base exception for every error raised by the Billit client.
 */
class BillitException extends RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly ?int $statusCode = null,
        private readonly ?stdClass $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    /**
     * The HTTP status code returned by the API, or null when no response was received.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * The decoded error body returned by the API, when there was one.
     */
    public function getResponseBody(): ?stdClass
    {
        return $this->responseBody;
    }
}
