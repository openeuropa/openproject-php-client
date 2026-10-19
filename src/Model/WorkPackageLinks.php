<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Keyed HAL link fragments for work-package relationships.
 *
 * @internal
 */
final class WorkPackageLinks
{
    /**
     * Builds the type relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function type(int $id): array
    {
        return ['type' => ['href' => '/api/v3/types/' . $id]];
    }

    /**
     * Builds the status relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function status(int $id): array
    {
        return ['status' => ['href' => '/api/v3/statuses/' . $id]];
    }

    /**
     * Builds the priority relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function priority(int $id): array
    {
        return ['priority' => ['href' => '/api/v3/priorities/' . $id]];
    }

    /**
     * Builds the project relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function project(int $id): array
    {
        return ['project' => ['href' => '/api/v3/projects/' . $id]];
    }

    /**
     * Builds the assignee relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function assignee(int $id): array
    {
        return ['assignee' => ['href' => '/api/v3/users/' . $id]];
    }

    /**
     * Builds the responsible relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function responsible(int $id): array
    {
        return ['responsible' => ['href' => '/api/v3/users/' . $id]];
    }

    /**
     * Builds the parent relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function parent(int $id): array
    {
        return ['parent' => ['href' => '/api/v3/work_packages/' . $id]];
    }

    /**
     * Builds the version relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function version(int $id): array
    {
        return ['version' => ['href' => '/api/v3/versions/' . $id]];
    }

    /**
     * Builds the category relationship fragment.
     *
     * @return array<string, array{href: string}>
     */
    public static function category(int $id): array
    {
        return ['category' => ['href' => '/api/v3/categories/' . $id]];
    }

    /**
     * Builds a reference-type custom field fragment by its number.
     *
     * @param string $resource
     *   The referenced resource path segment (e.g. `custom_options`, `users`,
     *   `versions`).
     *
     * @return array<string, array{href: string}>
     */
    public static function customField(int $number, int $id, string $resource): array
    {
        return ['customField' . $number => ['href' => '/api/v3/' . $resource . '/' . $id]];
    }
}
