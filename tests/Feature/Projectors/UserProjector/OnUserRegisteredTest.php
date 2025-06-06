<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserRegisteredTest extends TestCase
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
    public function user_registered(): void
    {
        // Arrange
        $event = new UserRegistered(
            Uuid::uuid7(),
            'hello@auditable-users.com',
            '123456',
        );

        $this->storedEventRepository->persist($event, Uuid::uuid7()->toString());

        // Act
        $this->projector->onUserRegistered($event);

        // Assert
        $this->assertDatabaseHas(User::class, [
            'uuid' => $event->userUuid,
            'email' => $event->email,
            'password' => $event->hashedPassword,
        ]);
    }
}
