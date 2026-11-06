# Billit client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Tests][ico-tests]][link-tests]
[![Software License][ico-license]](LICENSE.md)

The PHP client for the [billit.io API](https://billit.io/apidocs/introduction), with first-class Laravel support.
Create MyData (A.A.D.E.) compatible invoices, manage contacts, products, payments, purchases, tags and
One Click Payment links, and look up Greek companies by VAT id.

## Requirements

- PHP 8.2 or newer
- Guzzle 7.9 or newer
- Laravel 12 or 13 (optional, for the service provider and facade)

Upgrading from 2.x? See [UPGRADE.md](UPGRADE.md).

## Install

```bash
composer require drakakisgeo/billit
```

## Getting a token

Register for a [Sandbox account](https://app.sandbox-billit.xyz), go to **Settings > API** and generate a token.
Build and test against the sandbox first. Production tokens are read only until you contact Billit for full access.
Sandbox data is wiped every month.

## Usage

### Plain PHP

```php
use Drakakisgeo\Billit\Billit;

$billit = new Billit('your-token', sandbox: true);

$billit->welcome(); // { "msg": "Hello from the Billit API" }
```

The default HTTP client uses a 10 second request timeout and a 5 second connect timeout. To change them, or to add
your own Guzzle middleware, pass a client:

```php
$billit = new Billit('your-token', sandbox: true, client: Billit::httpClient(sandbox: true, timeout: 30));
```

### Laravel

The service provider and the `Billit` facade are auto-discovered. Add the credentials to `config/services.php`:

```php
'billit' => [
    'token' => env('BILLIT_API_TOKEN'),
    'sandbox' => env('BILLIT_API_IS_SANDBOX', false),
    'version' => 'v1',
    'timeout' => 10,          // seconds, optional
    'connect_timeout' => 5,   // seconds, optional
],
```

Then inject it, resolve it, or use the facade:

```php
use Drakakisgeo\Billit\Billit;

public function __construct(private Billit $billit) {}

$billit = app(Billit::class);

\Billit::myAccount();
```

## Responses

Every method returns the decoded JSON as a `stdClass`. Single resources live under `data`, and lists add
`links` and `meta` for pagination. Successful `DELETE` calls return an empty object.

## Errors

API and network failures throw an exception that extends `Drakakisgeo\Billit\Exceptions\BillitException`:

| Exception | When |
|---|---|
| `ValidationException` | 400 / 422. Use `errors()` to get the field errors |
| `AuthenticationException` | 401 / 403. The token is missing, invalid, lacks the `read`/`write` ability, or writes aren't approved in production yet |
| `NotFoundException` | 404 |
| `ConflictException` | 409. An invoice update sent `loaded_at` and the invoice changed since. Read it again and retry |
| `RateLimitException` | 429. The API allows 60 requests per minute. Use `retryAfter()` when the API sends it |
| `ServerException` | 5xx |
| `ConnectionException` | DNS failure, refused connection or timeout |
| `BillitException` | Anything else, including an invalid JSON response |

Every exception has `getStatusCode()` and `getResponseBody()`. For errors the API logged, `incidentId()` returns the
id to quote to Billit support.

```php
use Drakakisgeo\Billit\Exceptions\BillitException;
use Drakakisgeo\Billit\Exceptions\ValidationException;

try {
    $invoice = $billit->invoiceCreate($payload);
} catch (ValidationException $e) {
    $e->errors(); // ['customerId' => ['The customer id field is required.']]
} catch (BillitException $e) {
    report($e);
}
```

## Endpoints

| Resource | Methods |
|---|---|
| Account | `myAccount()` (cached per instance) |
| Contacts (customers & suppliers) | `contacts($params)`, `contactCreate($payload)`, `contactShow($id)`, `contactUpdate($id, $payload)`, `contactPatch($id, $payload)`, `contactDelete($id)` |
| Invoices | `invoices($params)`, `invoiceCreate($payload)`, `invoiceShow($uuid)`, `invoiceUpdate($uuid, $payload)`, `invoiceDelete($uuid)`, `alpSignature($payload)` |
| Products | `products($params)`, `productCreate($payload)`, `productShow($id)`, `productUpdate($id, $payload)`, `productDelete($id)` |
| One Click Payment links | `ocps($params)`, `ocpCreate($payload)`, `ocpShow($uuid)`, `ocpUpdate($uuid, $payload)`, `ocpDelete($uuid)` |
| Payments | `payments($params)`, `paymentCreate($payload)` (`paymentDelete($id)` is deprecated) |
| Purchases | `purchases($params)`, `purchaseCreate($payload)`, `purchaseShow($id)`, `purchaseDelete($id)` |
| Tags | `tags($params)`, `tagShow($id)`, `tagDelete($id)` |
| GSIS VAT lookup | `gsis($vatId)` |

List methods send `$params` as query string filters. All lists accept `per_page` (max 250) and `page`. The default page
size is 25 for invoices and contacts, 50 for products, 20 for payments, purchases and tags, and 15 for OCP links.

The `*Update()` methods use `PUT`, which replaces the whole resource: fields you leave out are cleared, not kept.
Read the resource first and send it back complete, or use `contactPatch()` to change only some fields of a contact.
See the [API reference](https://billit.io/apidocs/api/account) for each resource's fields and filters.

## Examples

### The account

The account response contains the biller, payment method and VAT ids you need when creating invoices. It rarely
changes, so it is safe to cache.

```php
$account = $billit->myAccount();
```

### Contacts

```php
$billit->contacts(['q' => 'Awesome', 'unpaid' => 1, 'per_page' => 100]);

$billit->contactCreate([
    'isCompany' => true,
    'company' => 'Awesome S.A.',
    'customerType' => 1, // 1 domestic, 2 EU, 3 outside the EU
    'contactType' => 1,  // 1 customer, 2 supplier, 3 both
    'country' => 'GR',
    'city' => 'Athens',
    'postalCode' => '10431',
    'inCharge' => 'Kalampakopoulos George',
    'lang' => 'el',
    'vatId' => 'EL075101010',
]);

$billit->contactPatch(1010, ['inCharge' => 'Papadopoulos Maria']); // only this field changes
$billit->contactShow(1010);
$billit->contactDelete(1010);
```

### Create an invoice (MyData enabled)

```php
$billit->invoiceCreate([
    'customerId' => 694,
    'sendMail' => false,
    'invoiceDate' => '2026-04-15',
    'invoiceTypeId' => 349,
    'isPaid' => true,
    'mydataInvoiceType' => '2.1',
    'taxes' => [
        ['taxId' => 514],
        ['taxId' => 524, 'taxVatShow' => 1],
    ],
    'products' => [
        [
            'productId' => null,
            'details' => 'Consulting',
            'measurementUnit' => 1,
            'vatId' => 376,
            'price' => 123.32,
            'quantity' => 1,
            'incomeClassificationCat' => 'category1_3',
            'incomeClassificationType' => 'E3_561_001',
        ],
    ],
    'tags' => ['billit', 'test-tag'],
    'mydataPayment' => [
        'paidAmount' => 0.32,
        'paymentMethod' => 3,
        'epiPistosi' => 10,
    ],
]);
```

### Update an invoice safely

`invoiceUpdate()` replaces the invoice, so send it complete. Pass the `updatedAt` you read as `loaded_at` to make
sure nobody changed it in the meantime:

```php
use Drakakisgeo\Billit\Exceptions\ConflictException;

try {
    $billit->invoiceUpdate($uuid, $payload + ['loaded_at' => $invoice->data->updatedAt]);
} catch (ConflictException $e) {
    // The invoice changed since you read it: read it again, then resend your update.
}
```

### One Click Payment link

You need an online payment method set up in the Billit app first. Use that provider's sandbox account while testing.

```php
$ocp = $billit->ocpCreate([
    'title' => 'Annual subscription',
    'cost' => 124.00,
    'netValue' => 100.00,
    'vatId' => 376,
    'invoiceTypeId' => 349,
    'paymentMethodId' => 12,
    'lang' => 'el',
    'expirationAt' => '2026-12-31',
]);
```

### Look up a company by VAT id

Enable GSIS in your Billit settings first.

```php
$company = $billit->gsis('094014201');
```

## Testing

```bash
composer test
composer analyse
```

## Security

Please report vulnerabilities as described in [SECURITY.md](SECURITY.md), not in public issues.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Drakakis George][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/drakakisgeo/billit.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-tests]: https://img.shields.io/github/actions/workflow/status/drakakisgeo/billit/tests.yml?branch=master&style=flat-square&label=tests
[link-packagist]: https://packagist.org/packages/drakakisgeo/billit
[link-tests]: https://github.com/drakakisgeo/billit/actions/workflows/tests.yml
[link-author]: https://github.com/drakakisgeo
[link-contributors]: ../../contributors
