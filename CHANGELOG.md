# Changelog

## 3.1.0

Brings the client in line with the updated [API reference](https://billit.io/apidocs/api/account).

### Added
- `contactPatch($id, $payload)` for `PATCH /contacts/{id}`, which changes only the fields you send
- `ConflictException` for 409 responses, sent when an invoice update includes `loaded_at` and the invoice changed since
- `incidentId()` on every exception, returning the `data.insidentId` the API logs unexpected errors under
- Docblocks for the documented list filters of `invoices()`, `ocps()` and `products()`

### Changed
- The `*Update()` docblocks and the README now warn that `PUT` replaces the whole resource
- README examples: `customerType` vs `contactType`, no deprecated `excludeMydata`

### Fixed
- 403 errors for missing token abilities now include the API's `msg` in the exception message

### Deprecated
- `paymentDelete()`. `DELETE /payments/{id}` is no longer documented. It will be removed in 4.0

## 3.0.0

See [UPGRADE.md](UPGRADE.md) for the breaking changes.

### Added
- One Click Payment links: `ocps()`, `ocpCreate()`, `ocpShow()`, `ocpUpdate()`, `ocpDelete()`
- `gsis($vatId)` for looking up Greek companies by VAT id
- `alpSignature($payload)` for POS signatures
- Typed exceptions: `ValidationException` (with `errors()`), `AuthenticationException`, `NotFoundException`,
  `RateLimitException` (with `retryAfter()`), `ServerException`, `ConnectionException`
- `Billit::httpClient()` and `Billit::baseUri()` helpers, and a `connect_timeout` setting
- Facade `@method` annotations for IDE autocompletion
- GitHub Actions CI covering PHP 8.2–8.5 and Laravel 12–13, Dependabot, PHPStan level 8

### Changed
- PHP 8.2+, Guzzle 7.9+, Laravel 12/13
- Errors throw exceptions instead of being returned as response objects
- List filters are sent as a query string, so they now work
- The Laravel binding is a singleton
- Default timeout raised to 10 seconds

### Fixed
- The `Billit` facade alias in `composer.json` pointed at a class that doesn't exist
- PHP 8.4 implicit nullable parameter deprecations
- `TypeError` when the API answered with an empty body
- 5xx responses and timeouts were silently returned as data

### Security
- Dropped Guzzle 6 and old Guzzle 7 releases that have known vulnerabilities
- The API token is marked `#[SensitiveParameter]`, so it stays out of stack traces
- Resource identifiers are URL encoded, so they can't change the request path

### Removed
- `suppliers()`, `supplierShow()`, `supplierDelete()`. Use the contacts methods
