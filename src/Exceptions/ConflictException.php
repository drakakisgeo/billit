<?php

namespace Drakakisgeo\Billit\Exceptions;

/**
 * The resource changed since you read it (409). Sent on invoice updates that include `loaded_at`
 * when the invoice was updated after that time. Read it again, then resend your update.
 */
class ConflictException extends BillitException
{
}
