<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Fluent builder that assembles a membership write payload.
 */
final class MembershipInput
{
    /**
     * @var array<string, mixed>
     */
    private array $properties = [];

    /**
     * @var array<string, array{href: string}|list<array{href: string}>>
     */
    private array $links = [];

    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

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
     * Sets the user the membership is granted to.
     *
     * The API models this relationship under the `principal` link key, since a
     * membership can also be granted to a group.
     */
    public function user(int $id): self
    {
        $this->links['principal'] = ['href' => '/api/v3/users/' . $id];

        return $this;
    }

    /**
     * Sets the project the membership is granted in.
     *
     * Omitting this call makes the membership global instead of scoped to a
     * project: the granted roles then apply across the whole instance.
     */
    public function project(int $id): self
    {
        $this->links['project'] = ['href' => '/api/v3/projects/' . $id];

        return $this;
    }

    /**
     * Sets the roles granted to the principal, replacing any previously set.
     */
    public function roles(int ...$ids): self
    {
        $roles = [];
        foreach ($ids as $id) {
            $roles[] = ['href' => '/api/v3/roles/' . $id];
        }

        $this->links['roles'] = $roles;

        return $this;
    }

    /**
     * Sets a custom notification message as a formattable raw value.
     *
     * Overwrites the standard notification sent to the principal.
     */
    public function notificationMessage(string $raw): self
    {
        $this->meta['notificationMessage'] = ['raw' => $raw];

        return $this;
    }

    /**
     * Sets whether to send the membership notification.
     *
     * The API sends it by default, to the principal or, for a group, to every
     * user in it.
     */
    public function sendNotification(bool $sendNotification): self
    {
        $this->meta['sendNotification'] = $sendNotification;

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

        if ($this->meta !== []) {
            $existing = isset($payload['_meta']) && is_array($payload['_meta']) ? $payload['_meta'] : [];
            $payload['_meta'] = $this->meta + $existing;
        }

        return $payload;
    }
}
