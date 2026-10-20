<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a work-package write payload.
 */
final class WorkPackageInput
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
     * Sets the subject.
     */
    public function subject(string $subject): self
    {
        $this->properties['subject'] = $subject;

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
     * Sets the start date.
     */
    public function startDate(\DateTimeInterface $date): self
    {
        $this->properties['startDate'] = $date->format('Y-m-d');

        return $this;
    }

    /**
     * Sets the due date.
     */
    public function dueDate(\DateTimeInterface $date): self
    {
        $this->properties['dueDate'] = $date->format('Y-m-d');

        return $this;
    }

    /**
     * Sets the estimated time as an ISO-8601 duration.
     */
    public function estimatedTime(string $duration): self
    {
        $this->properties['estimatedTime'] = $duration;

        return $this;
    }

    /**
     * Sets whether the work package is scheduled manually.
     */
    public function scheduleManually(bool $scheduleManually): self
    {
        $this->properties['scheduleManually'] = $scheduleManually;

        return $this;
    }

    /**
     * Sets the completion percentage.
     */
    public function percentageDone(int $percentageDone): self
    {
        $this->properties['percentageDone'] = $percentageDone;

        return $this;
    }

    /**
     * Sets the type relationship.
     */
    public function type(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::type($id));

        return $this;
    }

    /**
     * Sets the status relationship.
     */
    public function status(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::status($id));

        return $this;
    }

    /**
     * Sets the priority relationship.
     */
    public function priority(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::priority($id));

        return $this;
    }

    /**
     * Sets the project relationship.
     */
    public function project(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::project($id));

        return $this;
    }

    /**
     * Sets the assignee relationship.
     */
    public function assignee(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::assignee($id));

        return $this;
    }

    /**
     * Sets the responsible relationship.
     */
    public function responsible(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::responsible($id));

        return $this;
    }

    /**
     * Sets the parent relationship.
     */
    public function parent(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::parent($id));

        return $this;
    }

    /**
     * Sets the version relationship.
     */
    public function version(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::version($id));

        return $this;
    }

    /**
     * Sets the category relationship.
     */
    public function category(int $id): self
    {
        $this->links = array_merge($this->links, WorkPackageLinks::category($id));

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
