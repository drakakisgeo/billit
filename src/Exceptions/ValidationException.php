<?php

namespace Drakakisgeo\Billit\Exceptions;

/**
 * The request payload was rejected (400 / 422).
 */
class ValidationException extends BillitException
{
    /**
     * Field level validation errors, keyed by field name.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        $errors = $this->getResponseBody()->errors ?? [];

        return json_decode(json_encode($errors, JSON_THROW_ON_ERROR), true) ?: [];
    }
}
