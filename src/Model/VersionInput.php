<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a version write payload.
 */
final class VersionInput
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
     * Creates an instance from a payload array in the API's request format.
     *
     * @param array<string, mixed> $data
     *   The payload, for example a form's payload.
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
     * Sets the status.
     */
    public function status(VersionStatus $status): self
    {
        $this->properties['status'] = $status->value;

        return $this;
    }

    /**
     * Sets the sharing mode.
     */
    public function sharing(VersionSharing $sharing): self
    {
        $this->properties['sharing'] = $sharing->value;

        return $this;
    }

    /**
     * Sets the start date.
     */
    public function startDate(\DateTimeInterface $startDate): self
    {
        $this->properties['startDate'] = $startDate->format('Y-m-d');

        return $this;
    }

    /**
     * Sets the end date.
     */
    public function endDate(\DateTimeInterface $endDate): self
    {
        $this->properties['endDate'] = $endDate->format('Y-m-d');

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
     * Sets the defining project relationship.
     */
    public function definingProject(int $id): self
    {
        $this->links['definingProject'] = ['href' => '/api/v3/projects/' . $id];

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
     * Use this for custom fields whose value is a list option, user, version,
     * or hierarchy item, rather than a simple scalar.
     */
    public function customFieldLink(int $number, int $id, CustomFieldLinkResource $resource): self
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
