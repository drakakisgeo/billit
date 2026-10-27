<?php

namespace Drakakisgeo\Billit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \stdClass welcome()
 * @method static \stdClass myAccount()
 * @method static \stdClass contacts(array<string, mixed> $params = [])
 * @method static \stdClass contactCreate(array<string, mixed> $payload)
 * @method static \stdClass contactUpdate(int $resourceId, array<string, mixed> $payload)
 * @method static \stdClass contactShow(int $resourceId)
 * @method static \stdClass contactDelete(int $resourceId)
 * @method static \stdClass invoices(array<string, mixed> $params = [])
 * @method static \stdClass invoiceCreate(array<string, mixed> $payload)
 * @method static \stdClass invoiceUpdate(string $uuid, array<string, mixed> $payload)
 * @method static \stdClass invoiceShow(string $uuid)
 * @method static \stdClass invoiceDelete(string $uuid)
 * @method static \stdClass alpSignature(array<string, mixed> $payload)
 * @method static \stdClass products(array<string, mixed> $params = [])
 * @method static \stdClass productCreate(array<string, mixed> $payload)
 * @method static \stdClass productUpdate(int $resourceId, array<string, mixed> $payload)
 * @method static \stdClass productShow(int $resourceId)
 * @method static \stdClass productDelete(int $resourceId)
 * @method static \stdClass ocps(array<string, mixed> $params = [])
 * @method static \stdClass ocpCreate(array<string, mixed> $payload)
 * @method static \stdClass ocpUpdate(string $uuid, array<string, mixed> $payload)
 * @method static \stdClass ocpShow(string $uuid)
 * @method static \stdClass ocpDelete(string $uuid)
 * @method static \stdClass payments(array<string, mixed> $params = [])
 * @method static \stdClass paymentCreate(array<string, mixed> $payload)
 * @method static \stdClass paymentDelete(int $resourceId)
 * @method static \stdClass purchases(array<string, mixed> $params = [])
 * @method static \stdClass purchaseCreate(array<string, mixed> $payload)
 * @method static \stdClass purchaseShow(int $resourceId)
 * @method static \stdClass purchaseDelete(int $resourceId)
 * @method static \stdClass tags(array<string, mixed> $params = [])
 * @method static \stdClass tagShow(int $resourceId)
 * @method static \stdClass tagDelete(int $resourceId)
 * @method static \stdClass gsis(string $vatId)
 * @method static bool isSandbox()
 *
 * @see \Drakakisgeo\Billit\Billit
 */
class Billit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Drakakisgeo\Billit\Billit::class;
    }
}
