<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Commands\Register;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class RegisterTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_user(): void
    {
        $userUuid = Uuid::uuid7();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([
            ])
            ->when(function (UserAggregateRoot $user) use ($userUuid): void {
                $user->register(
                    new Register(
                        $userUuid,
                        'admin@auditable-users.com',
                        '123456'
                    )
                );
            })
            ->assertRecorded(
                new UserRegistered(
                    $userUuid,
                    'admin@auditable-users.com',
                    '123456'
                )
            );
    }
}
