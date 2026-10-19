<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A user in an OpenProject instance.
 */
final readonly class User
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $login,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?bool $admin,
        public ?string $status,
        public ?string $language,
        public ?string $avatar,
        public ?string $createdAt,
        public ?string $updatedAt,
        public CustomFields $customFields,
    ) {
    }

    /**
     * Creates an instance from a decoded API user response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('name'),
            $hal->getString('login'),
            $hal->getString('firstName'),
            $hal->getString('lastName'),
            $hal->getString('email'),
            $hal->getBool('admin'),
            $hal->getString('status'),
            $hal->getString('language'),
            $hal->getString('avatar'),
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
            CustomFields::fromApiResponse($data),
        );
    }
}
