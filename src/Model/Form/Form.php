<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model\Form;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\ValidationError;

/**
 * A form response containing the writable payload, schema, and validation errors.
 *
 * The form endpoints return HTTP 200 even when the payload has validation
 * errors, so callers must check the validation errors (or hasErrors()) rather
 * than relying on the status code.
 */
final readonly class Form
{
    /**
     * @internal
     *
     * @param array<string, mixed> $payload
     *   The raw writable payload from the form response.
     * @param array<string, SchemaProperty> $schema
     *   Schema properties keyed by property name, excluding meta keys.
     * @param array<string, ValidationError> $validationErrors
     *   Validation errors keyed by the name of the faulty property.
     */
    public function __construct(
        public array $payload,
        public array $schema,
        public array $validationErrors,
    ) {
    }

    /**
     * Returns true when the form response contains at least one validation error.
     */
    public function hasErrors(): bool
    {
        return $this->validationErrors !== [];
    }

    /**
     * Creates an instance from a decoded API form response.
     *
     * @param array<string, mixed> $data
     *   The decoded form response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);
        $embedded = $hal->getArray('_embedded');

        $rawPayload = $embedded['payload'] ?? null;
        /** @var array<string, mixed> $payload */
        $payload = is_array($rawPayload) ? $rawPayload : [];

        /** @var array<string, SchemaProperty> $schema */
        $schema = [];
        $rawSchema = $embedded['schema'] ?? null;
        if (is_array($rawSchema)) {
            foreach ($rawSchema as $key => $value) {
                if (str_starts_with((string) $key, '_')) {
                    continue;
                }
                if (!is_array($value)) {
                    continue;
                }
                /** @var array<string, mixed> $value */
                $schema[(string) $key] = SchemaProperty::fromApiResponse($value);
            }
        }

        /** @var array<string, ValidationError> $validationErrors */
        $validationErrors = [];
        $rawErrors = $embedded['validationErrors'] ?? null;
        if (is_array($rawErrors)) {
            foreach ($rawErrors as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }
                /** @var array<string, mixed> $value */
                $validationErrors[(string) $key] = ValidationError::fromApiResponse($value);
            }
        }

        return new self($payload, $schema, $validationErrors);
    }
}
