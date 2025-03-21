<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Commands\CreateResetToken;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokenCreated;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class CreateResetTokenTest extends TestCase
{
    use WithFaker;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_create_a_password_reset_token_record(): void
    {
        $userUuid = Uuid::uuid7();
        $token = $this->faker->password();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([
            ])
            ->when(function (UserAggregateRoot $user) use ($userUuid, $token): void {
                $user->createResetToken(
                    new CreateResetToken(
                        $userUuid,
                        $token
                    )
                );
            })
            ->assertRecorded(
                new UserResetTokenCreated(
                    $userUuid,
                    $token,
                )
            );
    }
}
