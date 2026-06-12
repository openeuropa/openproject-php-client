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

## Installation

```sh
composer require openeuropa/openproject-php-client

# Plus a PSR-18/PSR-17 implementation, if your project does not have one yet:
composer require guzzlehttp/guzzle
```

## Usage

The client speaks HAL+JSON to the OpenProject API v3 and authenticates with an
API token (HTTP Basic with the literal username `apikey`). Usage examples will
be added as the API surface stabilises.

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

# Run the test suite with a text coverage report.
docker compose run --rm php composer coverage
```

The container is built from `php:8.3-cli` (the minimum supported PHP version)
with Composer included; the image is built automatically on first run. Xdebug
is installed but off by default — the `coverage` script enables it for the
run, and `-e XDEBUG_MODE=debug` enables step debugging.

## License

Licensed under the [EUPL-1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12).
