<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Project::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Formattable::class)]
final class ProjectTest extends TestCase
{
    public function testDecodesProjectFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/project.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $project = Project::fromApiResponse($data);

        self::assertSame(1, $project->id);
        self::assertSame('project_example', $project->identifier);
        self::assertSame('Project example', $project->name);
        self::assertTrue($project->active);
        self::assertFalse($project->public);
        self::assertFalse($project->favorited);
        self::assertSame('2014-05-21T08:51:20.396Z', $project->createdAt);
        self::assertSame('2014-05-21T08:51:20.396Z', $project->updatedAt);

        self::assertInstanceOf(Formattable::class, $project->description);
        self::assertSame('markdown', $project->description->format);
        self::assertSame('Lorem **ipsum**', $project->description->raw);

        self::assertInstanceOf(Formattable::class, $project->statusExplanation);
        self::assertSame('markdown', $project->statusExplanation->format);
        self::assertSame('Everything **fine**', $project->statusExplanation->raw);
    }

    public function testAbsentFormattableFieldsAreNull(): void
    {
        $data = [
            '_type' => 'Project',
            'id' => 5,
            'name' => 'Minimal Project',
        ];

        $project = Project::fromApiResponse($data);

        self::assertSame(5, $project->id);
        self::assertSame('Minimal Project', $project->name);
        self::assertNull($project->description);
        self::assertNull($project->statusExplanation);
    }
}
