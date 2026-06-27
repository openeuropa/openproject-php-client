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
    public function startDate(string $date): self
    {
        $this->properties['startDate'] = $date;

        return $this;
    }

    /**
     * Sets the due date.
     */
    public function dueDate(string $date): self
    {
        $this->properties['dueDate'] = $date;

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
        $this->links['type'] = ['href' => '/api/v3/types/' . $id];

        return $this;
    }

    /**
     * Sets the status relationship.
     */
    public function status(int $id): self
    {
        $this->links['status'] = ['href' => '/api/v3/statuses/' . $id];

        return $this;
    }

    /**
     * Sets the priority relationship.
     */
    public function priority(int $id): self
    {
        $this->links['priority'] = ['href' => '/api/v3/priorities/' . $id];

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
     * Sets the assignee relationship.
     */
    public function assignee(int $id): self
    {
        $this->links['assignee'] = ['href' => '/api/v3/users/' . $id];

        return $this;
    }

    /**
     * Sets the responsible relationship.
     */
    public function responsible(int $id): self
    {
        $this->links['responsible'] = ['href' => '/api/v3/users/' . $id];

        return $this;
    }

    /**
     * Sets the parent relationship.
     */
    public function parent(int $id): self
    {
        $this->links['parent'] = ['href' => '/api/v3/work_packages/' . $id];

        return $this;
    }

    /**
     * Sets the version relationship.
     */
    public function version(int $id): self
    {
        $this->links['version'] = ['href' => '/api/v3/versions/' . $id];

        return $this;
    }

    /**
     * Sets the category relationship.
     */
    public function category(int $id): self
    {
        $this->links['category'] = ['href' => '/api/v3/categories/' . $id];

        return $this;
    }

    /**
     * Sets a custom field value by its number.
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
            $payload['_links'] = $existing + $this->links;
        }

        return $payload;
    }
}
