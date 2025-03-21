<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPostLoginDataCreated;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserPostLoginDataCreatedTest extends TestCase
{
    use WithFaker;

    private UserProjector $projector;

    private StoredEventRepository $storedEventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projector = $this->app->make(UserProjector::class);
        $this->storedEventRepository = $this->app->make(StoredEventRepository::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_post_login_data_created(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'remember_token' => null,
        ])->writeable())->save();

        $event = new UserPostLoginDataCreated(
            $user->uuid,
            $this->faker->userAgent(),
            $this->faker->ipv4(),
            $this->faker->email(),
            $this->faker->sha256(),
        );

        $this->storedEventRepository->persist($event, $event->userUuid->toString());

        // Act
        $this->projector->onUserPostLoginDataCreated($event);

        // Assert
        $this->assertDatabaseHas(User::class, [
            'uuid' => $event->userUuid,
            'remember_token' => $event->rememberToken,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_a_null_remember_token(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'remember_token' => $this->faker->sha256(),
        ])->writeable())->save();

        $event = new UserPostLoginDataCreated(
            $user->uuid,
            $this->faker->userAgent(),
            $this->faker->ipv4(),
            $this->faker->email(),
            null,
        );

        $this->storedEventRepository->persist($event, $event->userUuid->toString());

        // Act
        $this->projector->onUserPostLoginDataCreated($event);

        // Assert
        $this->assertDatabaseHas(User::class, [
            'uuid' => $event->userUuid,
            'remember_token' => null,
        ]);
    }
}
