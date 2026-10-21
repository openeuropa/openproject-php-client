<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a time entry write payload.
 */
final class TimeEntryInput
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
     * Sets the date the expenditure is booked for.
     */
    public function spentOn(\DateTimeInterface $spentOn): self
    {
        $this->properties['spentOn'] = $spentOn->format('Y-m-d');

        return $this;
    }

    /**
     * Sets the time quantifying the expenditure, as an ISO 8601 duration.
     */
    public function hours(string $hours): self
    {
        $this->properties['hours'] = $hours;

        return $this;
    }

    /**
     * Sets whether the time entry is actively tracking time.
     */
    public function ongoing(bool $ongoing): self
    {
        $this->properties['ongoing'] = $ongoing;

        return $this;
    }

    /**
     * Sets the start time.
     *
     * Writable only when the instance has start and end time tracking enabled.
     * The start must fall on the spentOn date in the time zone of the time
     * entry's user.
     */
    public function startTime(\DateTimeInterface $startTime): self
    {
        $this->properties['startTime'] = $startTime->format(\DateTimeInterface::RFC3339);

        return $this;
    }

    /**
     * Sets the comment as a formattable raw value.
     */
    public function comment(string $raw): self
    {
        $this->properties['comment'] = ['raw' => $raw];

        return $this;
    }

    /**
     * Sets the project relationship.
     */
    public function project(int $id): self
    {
        $this->links['project'] = ['href' => '/api/v3/projects/' . $id];

        return $this;
    }

    /**
     * Sets the work package the time entry is logged on.
     *
     * The API models this relationship under the `entity` link key, since a
     * time entry can also be logged on a meeting.
     */
    public function workPackage(int $id): self
    {
        $this->links['entity'] = ['href' => '/api/v3/work_packages/' . $id];

        return $this;
    }

    /**
     * Sets the user the time entry tracks expenditures for.
     */
    public function user(int $id): self
    {
        $this->links['user'] = ['href' => '/api/v3/users/' . $id];

        return $this;
    }

    /**
     * Sets the time entry activity the time entry is categorized as.
     */
    public function activity(int $id): self
    {
        $this->links['activity'] = ['href' => '/api/v3/time_entries/activities/' . $id];

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
