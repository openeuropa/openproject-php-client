# OpenProject PHP Client

PHP client for the [OpenProject API v3](https://www.openproject.org/docs/api/).

> **Status: under development.** The public API is not stable yet and the
> package has not been published to Packagist.

## Requirements

- PHP >= 8.3
- A [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client and
  [PSR-17](https://www.php-fig.org/psr/psr-17/) factories of your choice
  (e.g. [Guzzle 7](https://github.com/guzzle/guzzle)). The library does not
  depend on a concrete HTTP client implementation.
- The PSR-18 client should be configured to follow HTTP redirects: some
  functionality, such as attachment downloads, relies on them. It should also
  strip the `Authorization` header when a redirect points to a different host,
  so your API token is never forwarded to a third party. Guzzle 7 and Symfony's
  HttpClient both do this by default.

## Installation

```sh
composer require openeuropa/openproject-php-client

# Plus a PSR-18/PSR-17 implementation, if your project does not have one yet:
composer require guzzlehttp/guzzle
```

## Usage

The client speaks HAL+JSON to the OpenProject API v3. Every resource is
reached through a method on `Client` (`$client->projects()`,
`$client->workPackages()`, ...) that returns an endpoint object; responses
decode to immutable, readonly DTOs.

### Authentication

`ApiKeyAuthentication` takes an API token, generated in the OpenProject web
UI under *My account > Access tokens*:

```php
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;

$authentication = new ApiKeyAuthentication($apiKey);
```

The token inherits the permissions of the user who generated it, so that
user's access is what the client can read and write.

The OpenProject API also accepts Bearer tokens and OAuth2, but the API key
is the only strategy this library ships. To use another, implement
`AuthenticationInterface` and pass your own instance to `Client`.

### Constructing the client

This library never uses auto-discovery (`php-http/discovery` or otherwise) to
find an HTTP layer. You inject the authentication strategy, a PSR-18 client,
and both PSR-17 factories explicitly:

```php
use OpenEuropa\OpenProjectClient\Client;

$client = new Client(
    'https://example.org',
    $authentication,
    new \GuzzleHttp\Client(),            // Symfony: new \Symfony\Component\HttpClient\Psr18Client()
    new \GuzzleHttp\Psr7\HttpFactory(),  // Symfony: new \Nyholm\Psr7\Factory\Psr17Factory()
    new \GuzzleHttp\Psr7\HttpFactory(),  // Symfony: new \Nyholm\Psr7\Factory\Psr17Factory()
);
```

Guzzle is only the concrete example here (see [Installation](#installation));
any PSR-18 client and PSR-17 request/stream factories work. The Symfony
alternatives come from `symfony/http-client` and `nyholm/psr7`.

The base URL is the instance root, without `/api/v3` (`https://example.org`,
not `https://example.org/api/v3`). Subpath-hosted instances work the same way
(`https://example.org/openproject`).

### Quick start

`info()` is the smallest authenticated call the API offers (`GET /api/v3`),
so it doubles as a connectivity and credentials smoke test.

```php
$info = $client->info();
$info->instanceName; // e.g. "My OpenProject instance"
```

Representative reads:

```php
$project = $client->projects()->get(1);
$workPackage = $client->workPackages()->get(42);
```

### Pagination

Collection endpoints expose up to two read shapes, depending on how the API
models the collection:

| Endpoints | Methods |
| --- | --- |
| `statuses()`, `types()`, `priorities()` | `all(): PageResults` only. The API models these collections as unpaginated. |
| `projects()`, `users()`, `workPackages()`, `timeEntries()`, `memberships()` | `all(?FilterSet, ?CollectionParams): \Generator` and `listPage(?FilterSet, ?CollectionParams): PageResults` |
| `relations()`, `versions()` | `all(?FilterSet $filters = null): PageResults` only. Filterable, but unpaginated in the same way. |

`all()` on the generator-backed endpoints paginates automatically:

```php
foreach ($client->workPackages()->all() as $workPackage) {
    $workPackage->subject;
}
```

`listPage()` returns a single `PageResults` instead, for callers that need
the totals or control over paging. It carries `total`, `count`,
`elements`, `pageSize`, `offset`, and `nextByOffset` (the raw link the API
uses to signal whether another page exists), and it is itself iterable:

```php
$page = $client->workPackages()->listPage();
$page->total; // count across every page, not just this page's elements

foreach ($page as $workPackage) {
    // Only this page's elements.
}
```

`workPackages()->all()` and `listPage()` default to an empty filter set
rather than no filters at all, so omitting `$filters` returns every work
package, not just the open ones the raw API defaults to.

Some endpoints add further listing methods scoped to a parent resource
(`workPackages()->listByProject()`, `types()->allByProject()`,
`categories()->allByProject()`, `versions()->allByProject()`,
`activities()->allByWorkPackage()`); each returns the same shape as its
unscoped sibling.

### Filters

`FilterSet` composes one or more `Filter` instances into the ANDed `filters`
query parameter the API expects:

```php
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

$filters = new FilterSet(
    new Filter('status', FilterOperator::EQUAL, ['1']),
    new Filter('assignee', FilterOperator::IS_NULL, null),
);

$page = $client->workPackages()->listPage($filters);
```

Filter values are always strings, in the shape the API's filter grammar
expects rather than PHP's native types: an id filters as `"5"`, not `5`;
a boolean filters as `"t"` or `"f"`, not `true`/`false`. Operators that take
no values (`IS_NULL`, `NOT_NULL`, `TODAY`, and similar) take `null` rather
than an empty array.

`CollectionParams` covers the non-filter query parameters:

```php
use OpenEuropa\OpenProjectClient\Query\CollectionParams;

$params = new CollectionParams(
    offset: 2,               // 1-based page number
    pageSize: 25,
    sortBy: [['id', 'desc']],
    groupBy: 'status',
    showSums: true,
);

$page = $client->workPackages()->listPage($filters, $params);
```

### The two-step form flow

Every writable resource validates through a form endpoint before you commit
the write: `createForm()` (and `updateForm()` for existing resources) posts
a payload for validation and returns a `Form` carrying the (possibly
corrected) `payload`, the `schema` (allowed values and writability per
property, as `SchemaProperty`), and any `validationErrors` keyed by
property name. `create()` and `update()` do not run the form step for you.

Please note that **form endpoints answer HTTP 200 even when the payload is
invalid**. Check `$form->hasErrors()`, not the response status.

```php
use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;

$input = (new WorkPackageInput())
    ->subject('Investigate flaky test')
    ->project($projectId)
    ->type($typeId);

$form = $client->workPackages()->createForm($input);

if ($form->hasErrors()) {
    foreach ($form->validationErrors as $property => $error) {
        // $error->attribute, $error->message, $error->identifier
    }
} else {
    $workPackage = $client->workPackages()->create($input);
}
```

The `*Input` classes (`WorkPackageInput`, `ProjectInput`, `RelationInput`,
and so on) are fluent builders for the write payload; `createForm()` and
`create()` accept the same instance.

### Exceptions

Every exception the client throws extends `OpenProjectException` (itself a
`\RuntimeException`). PSR-18 exceptions from the injected HTTP client are
mapped too, so nothing else reaches your code.

Status-carrying failures extend the intermediate `ApiException`, which
exposes `httpStatus` and `errorIdentifier`:

| HTTP status | Exception |
| --- | --- |
| 401 | `AuthenticationException` |
| 403 | `ForbiddenException` |
| 404 | `NotFoundException` |
| 409 | `ConflictException` |
| 422 | `ValidationException` (adds `errors`, a `list<ValidationError>`) |
| any other 4xx | `ApiErrorException` |

Two more sit directly under `OpenProjectException`, not `ApiException`:
`ApiUnreachableException` (a network-level failure the PSR-18 client raised,
or an HTTP >= 500 response) and `UnexpectedResponseException` (a response
body that does not match what the API contract promised).

```php
use OpenEuropa\OpenProjectClient\Exception\ValidationException;

try {
    $client->workPackages()->create($input);
} catch (ValidationException $e) {
    foreach ($e->errors as $error) {
        // $error->attribute, $error->message, $error->identifier
    }
}
```

### Write-side notes

- `workPackages()->update()` requires the work package's current
  `lockVersion` and raises `ConflictException` when it is stale. Read the
  work package first and pass back the `lockVersion` you got from that read:

  ```php
  $workPackage = $client->workPackages()->get(42);

  $updated = $client->workPackages()->update(
      $workPackage->id,
      (new WorkPackageInput())->subject('New subject'),
      $workPackage->lockVersion,
  );
  ```
- Work-package `create()`/`update()` and `activities()->comment()` all take
  a trailing `$notify = true` parameter controlling whether OpenProject
  notifies the involved users.
- `relations()->create(int $workPackageId, RelationInput $payload)` is
  scoped to a work package, unlike the other endpoints' `create()` methods.
- `projects()->delete()` is asynchronous: it returns as soon as the project
  is archived, and the row disappears shortly after via a background job.
  Every other endpoint's `delete()` is synchronous.
- Attachments upload through `attachments()->upload()` (standalone) or
  `attachments()->uploadToWorkPackage()`, and download through
  `attachments()->download()`, which returns a PSR-7 `StreamInterface`. See
  [Requirements](#requirements) for the redirect-following requirement this
  relies on.

## Development

All tooling runs in Docker; no local PHP installation is needed.

```sh
# Install dependencies.
docker compose run --rm php composer install

# Check coding standards (PSR-12).
docker compose run --rm php composer phpcs

# Auto-fix coding standard violations.
docker compose run --rm php composer phpcbf

# Run static analysis (PHPStan, level max).
docker compose run --rm php composer phpstan

# Run the test suite (PHPUnit).
docker compose run --rm php composer test

# Run the test suite with a coverage report; fails below 100% coverage.
docker compose run --rm php composer coverage
```

The container is built from `php:8.3-cli` (the minimum supported PHP version)
with Composer included; the image is built automatically on first run. Xdebug
is installed but off by default — the `coverage` script enables it for the
run, and `-e XDEBUG_MODE=debug` enables step debugging.

### Integration tests

`composer test` runs the unit suite only (mock-based, no server). A separate
`integration` suite drives the client against a real, throwaway OpenProject
instance and skips unless `OPENPROJECT_API_TOKEN` is set. Bring the instance up
behind its compose profile and pass the token (the base URL and Host header
default to the compose instance, so nothing else is needed):

```sh
docker compose --profile openproject up -d        # first boot seeds, minutes
scripts/wait-for-openproject.sh                    # poll until the API answers
docker compose --profile openproject run --rm \
  -e OPENPROJECT_API_TOKEN="$(scripts/generate-api-token.sh)" \
  php composer test:integration
docker compose --profile openproject down -v       # throw the instance away
```

## License

Licensed under the [EUPL-1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12).
