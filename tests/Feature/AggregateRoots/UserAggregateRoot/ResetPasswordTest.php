<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Commands\ResetPassword;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class ResetPasswordTest extends TestCase
{
    use WithFaker;

    #[\PHPUnit\Framework\Attributes\Test]
    public function resets_password(): void
    {
        $userUuid = Uuid::uuid7();
        $password = $this->faker->password();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([
            ])
            ->when(function (UserAggregateRoot $user) use ($userUuid, $password): void {
                $user->resetPassword(new ResetPassword(
                    $userUuid,
                    $password
                ));
            })
            ->assertRecorded(
                new UserPasswordReset(
                    $userUuid,
                    $password
                )
            );
    }
}
