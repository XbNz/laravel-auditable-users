<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserPasswordResetTest extends TestCase
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
    public function user_password_is_reset(): void
    {
        // Arrange
        $user = tap(User::factory()->make()->writeable())->save();

        $event = new UserPasswordReset(
            $user->uuid,
            $password = $this->faker->password(),
        );

        $this->storedEventRepository->persist($event, $user->uuid->toString());

        // Act
        $this->projector->onUserPasswordReset($event);

        // Assert
        $this->assertDatabaseHas(User::class, [
            'uuid' => $user->uuid,
            'password' => $password,
        ]);
    }
}
