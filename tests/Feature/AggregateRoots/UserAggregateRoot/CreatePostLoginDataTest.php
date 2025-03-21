<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Commands\CreatePostLoginData;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPostLoginDataCreated;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class CreatePostLoginDataTest extends TestCase
{
    use WithFaker;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_post_login_data_record(): void
    {
        $userUuid = Uuid::uuid7();
        $userAgent = $this->faker->userAgent();
        $ipAddress = $this->faker->ipv4();
        $email = $this->faker->email();
        $rememberToken = $this->faker->sha256();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([
            ])
            ->when(function (UserAggregateRoot $user) use ($userUuid, $userAgent, $ipAddress, $email, $rememberToken): void {
                $user->createPostLoginData(
                    new CreatePostLoginData(
                        $userUuid,
                        $userAgent,
                        $ipAddress,
                        $email,
                        $rememberToken
                    )
                );
            })
            ->assertRecorded(
                new UserPostLoginDataCreated(
                    $userUuid,
                    $userAgent,
                    $ipAddress,
                    $email,
                    $rememberToken
                )
            );
    }
}
