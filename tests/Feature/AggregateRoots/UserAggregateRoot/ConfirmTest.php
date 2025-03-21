<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\InvalidArgumentException;
use XbNz\LaravelAuditableUsers\Commands\Confirm;
use XbNz\LaravelAuditableUsers\StoredEvents\UserConfirmed;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class ConfirmTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_confirms_a_user(): void
    {
        $userUuid = Uuid::uuid7();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([

            ])
            ->when(function (UserAggregateRoot $userAggregateRoot) use ($userUuid): void {
                $userAggregateRoot->confirm(
                    new Confirm(
                        $userUuid,
                        'admin@auditable-users.com'
                    )
                );
            })
            ->assertRecorded(
                new UserConfirmed(
                    $userUuid,
                    'admin@auditable-users.com'
                )
            );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function if_a_user_is_already_verified_nothing_is_recorded(): void
    {
        // Arrange
        $userUuid = Uuid::uuid7();

        $aggregate = UserAggregateRoot::fake($userUuid->toString())
            ->given([
                new UserConfirmed(
                    $userUuid,
                    'admin@auditable-users.com',
                ),
            ]);

        // Act
        try {
            $aggregate->when(function (UserAggregateRoot $userAggregateRoot) use ($userUuid): void {
                $userAggregateRoot->confirm(
                    new Confirm(
                        $userUuid,
                        'admin@auditable-users.com',
                    )
                );
            });
        } catch (InvalidArgumentException $exception) {
            // Assert
            $aggregate->assertNothingRecorded();

            return;
        }

        $this->fail('Expected exception not thrown');
    }
}
