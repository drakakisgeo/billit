# Upgrade guide

## From 2.x to 3.0

### Requirements

- PHP 8.2+ (was 7.3+)
- Guzzle 7.9+. Guzzle 6 is end of life and has known vulnerabilities
- Laravel 12 or 13 for the service provider. Every Laravel 11 release has unpatched security advisories

### Errors now throw exceptions

In 2.x, failed requests returned the error body (or `{"error": "..."}` for timeouts and 5xx responses) as a normal
return value. In 3.0 every failure throws a `Drakakisgeo\Billit\Exceptions\BillitException` subclass. See the README
for the full list.

```php
// 2.x
$response = $client->invoiceCreate($payload);
if (isset($response->statusCode) && $response->statusCode === 422) { /* ... */ }

// 3.0
try {
    $response = $client->invoiceCreate($payload);
} catch (ValidationException $e) {
    $e->errors();
}
```

### A missing token fails earlier

`new Billit('')` throws an `InvalidArgumentException` from the constructor, not on the first request.

### List filters are sent as a query string

`contacts()`, `invoices()`, `products()` and the other list methods used to send `$params` as a JSON body on a GET
request, so the API ignored filters such as `per_page`, `q` or `tagid`. They are now sent as a query string. If you
relied on getting unfiltered results while passing filters, review those calls.

### Removed methods

| Removed | Use instead |
|---|---|
| `suppliers()`, `supplierShow()`, `supplierDelete()` | `contacts()`, `contactShow()`, `contactDelete()`. Suppliers are contacts |

### Return types

Every endpoint method returns `stdClass`. `DELETE` calls return an empty object instead of `null`, and list methods
take `array $params = []` instead of `?array $params = null`.

### Constructor

The constructor accepts any `GuzzleHttp\ClientInterface` (it used to require `GuzzleHttp\Client`), and the version
must look like `v1`. Use `Billit::httpClient()` to build a preconfigured client with a custom timeout.

### Laravel

- The client is a singleton, bound as `Drakakisgeo\Billit\Billit` with a `billit` alias. `resolve('billit')` still works
- The `Billit` facade alias now registers through package discovery. In 2.x it pointed at a class that doesn't exist
- New optional `connect_timeout` key in `services.billit`. The default timeout is now 10 seconds (was 2)
