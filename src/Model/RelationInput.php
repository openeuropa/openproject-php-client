<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a relation write payload.
 */
final class RelationInput
{
    /**
     * @var array<string, mixed>
     */
    private array $properties = [];

    /**
     * @var array<string, array{href: string}>
     */
    private array $links = [];

    /**
     * Creates an instance seeded from an existing payload array.
     *
     * @param array<string, mixed> $data
     *   The payload array, in the shape toArray() produces.
     */
    public static function fromArray(array $data): self
    {
        $input = new self();
        $input->properties = $data;

        return $input;
    }

    /**
     * Sets the relation type (e.g. relates, follows, precedes, blocks, duplicates).
     */
    public function type(string $type): self
    {
        $this->properties['type'] = $type;

        return $this;
    }

    /**
     * Sets the description.
     */
    public function description(string $description): self
    {
        $this->properties['description'] = $description;

        return $this;
    }

    /**
     * Sets the lag, in days, between the related work packages.
     */
    public function lag(int $lag): self
    {
        $this->properties['lag'] = $lag;

        return $this;
    }

    /**
     * Sets the target work package relationship.
     */
    public function to(int $id): self
    {
        $this->links['to'] = ['href' => '/api/v3/work_packages/' . $id];

        return $this;
    }

    /**
     * Sets an arbitrary top-level property.
     *
     * @param mixed $value
     *   The property value.
     */
    public function set(string $key, mixed $value): self
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Returns the payload array for the API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = $this->properties;

        if ($this->links !== []) {
            $existing = isset($payload['_links']) && is_array($payload['_links']) ? $payload['_links'] : [];
            $payload['_links'] = $this->links + $existing;
        }

        return $payload;
    }
}
