<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Membership;
use OpenEuropa\OpenProjectClient\Model\MembershipInput;
use OpenEuropa\OpenProjectClient\Model\Project;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MembershipsTest extends IntegrationTestCase
{
    /**
     * IDs of projects created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdProjectIds = [];

    /**
     * IDs of memberships created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdMembershipIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdMembershipIds as $id) {
            try {
                $this->client->memberships()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        foreach ($this->createdProjectIds as $id) {
            try {
                $this->client->projects()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        parent::tearDown();
    }

    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->memberships()->get(999999);
    }

    public function testListPageDecodesMemberships(): void
    {
        $results = $this->client->memberships()->listPage();

        // The seeded instance may legitimately have no memberships visible
        // to the requesting token, so the count-matches-elements check holds
        // regardless of how many exist.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $membership) {
            self::assertInstanceOf(Membership::class, $membership);
            self::assertGreaterThan(0, $membership->id);
        }
    }

    public function testAllGeneratorYieldsEveryMembership(): void
    {
        $total = $this->client->memberships()->listPage()->total;

        $yielded = 0;
        foreach ($this->client->memberships()->all() as $membership) {
            self::assertGreaterThan(0, $membership->id);
            $yielded++;
        }

        self::assertSame($total, $yielded);
    }

    public function testGetReturnsMembershipFromTheFirstListed(): void
    {
        $results = $this->client->memberships()->listPage();
        if ($results->elements === []) {
            self::markTestSkipped('The instance has no memberships visible to the requesting token.');
        }

        $first = $results->elements[0];
        $fetched = $this->client->memberships()->get($first->id);

        self::assertSame($first->id, $fetched->id);
    }

    public function testCreatePersistsMembershipAndCanBeReadBack(): void
    {
        $project = $this->createProject();
        $roleId = $this->firstProjectRoleId();

        $created = $this->createMembership($project->id, $roleId);

        self::assertSame($project->id, $created->project?->id());
        self::assertSame($roleId, $created->roles[0]->id());

        $fetched = $this->client->memberships()->get($created->id);
        self::assertSame($project->id, $fetched->project?->id());

        $this->client->memberships()->delete($created->id);

        $this->expectException(NotFoundException::class);
        $this->client->memberships()->get($created->id);
    }

    public function testCreateFormReturnsFormWithSchema(): void
    {
        $form = $this->client->memberships()->createForm();
        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateFormReturnsFormWithSchema(): void
    {
        $project = $this->createProject();
        $roleId = $this->firstProjectRoleId();
        $created = $this->createMembership($project->id, $roleId);

        $form = $this->client->memberships()->updateForm(
            $created->id,
            (new MembershipInput())->roles($roleId),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    /**
     * Asserts the edit form echoes back the submitted notification message.
     *
     * The message is not stored on the membership, so the form response is
     * the only place it can be read back, and it proves the meta block
     * reaches the API in the expected shape.
     */
    public function testUpdateFormRoundTripsNotificationMessage(): void
    {
        $project = $this->createProject();
        $roleId = $this->firstProjectRoleId();
        $created = $this->createMembership($project->id, $roleId);

        $message = 'phpclient notification ' . uniqid();
        $form = $this->client->memberships()->updateForm(
            $created->id,
            (new MembershipInput())->roles($roleId)->notificationMessage($message),
        );

        $payload = $form->payload;
        self::assertArrayHasKey('_meta', $payload);
        self::assertIsArray($payload['_meta']);
        /** @var array<string, mixed> $meta */
        $meta = $payload['_meta'];

        self::assertArrayHasKey('notificationMessage', $meta);
        self::assertIsArray($meta['notificationMessage']);
        /** @var array<string, mixed> $notificationMessage */
        $notificationMessage = $meta['notificationMessage'];

        self::assertSame($message, $notificationMessage['raw'] ?? null);
    }

    /**
     * Asserts a successful edit answers with the persisted membership.
     *
     * Roles are the membership's only stored attribute and the instance
     * grants a single one, so the payload restates the role the membership
     * already holds.
     */
    public function testUpdateReturnsThePersistedMembership(): void
    {
        $project = $this->createProject();
        $roleId = $this->firstProjectRoleId();
        $created = $this->createMembership($project->id, $roleId);

        $updated = $this->client->memberships()->update(
            $created->id,
            (new MembershipInput())->roles($roleId),
        );

        self::assertSame($created->id, $updated->id);
        self::assertSame($project->id, $updated->project?->id());
        self::assertCount(1, $updated->roles);
        self::assertSame($roleId, $updated->roles[0]->id());
    }

    public function testUpdateWithEmptyRolesThrowsValidationException(): void
    {
        $project = $this->createProject();
        $roleId = $this->firstProjectRoleId();
        $created = $this->createMembership($project->id, $roleId);

        $this->expectException(ValidationException::class);
        $this->client->memberships()->update($created->id, (new MembershipInput())->roles());
    }

    private function createProject(): Project
    {
        $suffix = uniqid();

        $project = $this->client->projects()->create(
            (new ProjectInput())->name('phpclient project ' . $suffix)->identifier('phpclient-' . $suffix),
        );

        $this->createdProjectIds[] = $project->id;

        return $project;
    }

    /**
     * Creates a membership for the instance's first user in the given project.
     */
    private function createMembership(int $projectId, int $roleId): Membership
    {
        $membership = $this->client->memberships()->create(
            (new MembershipInput())->project($projectId)->user($this->firstUserId())->roles($roleId),
        );

        $this->createdMembershipIds[] = $membership->id;

        return $membership;
    }

    /**
     * Derives a role id already granted on a project membership.
     *
     * There is no roles endpoint on this client, so a role id usable for a
     * project membership is derived from a seeded project membership rather
     * than hardcoded. The seed grants a single role that way, which is why no
     * test here changes a membership's role set.
     */
    private function firstProjectRoleId(): int
    {
        foreach ($this->client->memberships()->all() as $membership) {
            if ($membership->project?->id() === null || $membership->roles === []) {
                continue;
            }

            $roleId = $membership->roles[0]->id();
            if ($roleId !== null) {
                return $roleId;
            }
        }

        self::markTestSkipped('The instance has no project membership to derive a role id from.');
    }

    private function firstUserId(): int
    {
        $users = $this->client->users()->listPage();
        self::assertNotEmpty($users->elements, 'The instance must have at least one user.');

        return $users->elements[0]->id;
    }
}
