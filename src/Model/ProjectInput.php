<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a project write payload.
 */
final class ProjectInput
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
     * Sets the name.
     */
    public function name(string $name): self
    {
        $this->properties['name'] = $name;

        return $this;
    }

    /**
     * Sets the identifier.
     */
    public function identifier(string $identifier): self
    {
        $this->properties['identifier'] = $identifier;

        return $this;
    }

    /**
     * Sets whether the project is active.
     */
    public function active(bool $active): self
    {
        $this->properties['active'] = $active;

        return $this;
    }

    /**
     * Sets whether the project is public.
     */
    public function public(bool $public): self
    {
        $this->properties['public'] = $public;

        return $this;
    }

    /**
     * Sets the description as a formattable raw value.
     */
    public function description(string $raw): self
    {
        $this->properties['description'] = ['raw' => $raw];

        return $this;
    }

    /**
     * Sets the status explanation as a formattable raw value.
     */
    public function statusExplanation(string $raw): self
    {
        $this->properties['statusExplanation'] = ['raw' => $raw];

        return $this;
    }

    /**
     * Sets the parent relationship.
     */
    public function parent(int $id): self
    {
        $this->links['parent'] = ['href' => '/api/v3/projects/' . $id];

        return $this;
    }

    /**
     * Sets the status relationship.
     *
     * The identifier is the project-status slug (e.g. `on_track`).
     */
    public function status(string $identifier): self
    {
        $this->links['status'] = ['href' => '/api/v3/project_statuses/' . $identifier];

        return $this;
    }

    /**
     * Sets a simple custom field value by its number, as a top-level property.
     *
     * @param mixed $value
     *   The custom field value.
     */
    public function customField(int $number, mixed $value): self
    {
        $this->properties['customField' . $number] = $value;

        return $this;
    }

    /**
     * Sets a reference-type custom field by its number, as a link.
     *
     * Use this for custom fields whose value is a list option, user, or
     * version, rather than a simple scalar.
     *
     * @param string $resource
     *   The referenced resource path segment (e.g. `custom_options`, `users`,
     *   `versions`).
     */
    public function customFieldLink(int $number, int $id, string $resource): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::customField($number, $id, $resource));

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
     * @internal
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
