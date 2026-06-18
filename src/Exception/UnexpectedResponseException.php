<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * Thrown when an API response is missing a field the spec marks as required.
 *
 * A read DTO only models a field as non-nullable when the OpenProject API
 * spec marks it required; if such a field is absent or has the wrong type in
 * a response, the response does not match the contract the client was built
 * against (a malformed body or API drift), so decoding fails loudly here
 * rather than yielding a half-constructed DTO.
 */
final class UnexpectedResponseException extends OpenProjectException
{
}
