<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserConfirmed;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserConfirmedTest extends TestCase
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
    public function user_confirmed(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email_verified_at' => null,
        ])->writeable())->save();

        $event = new UserConfirmed(
            $user->uuid,
            $user->email,
        );

        $this->storedEventRepository->persist($event, $user->uuid->toString());

        // Act
        $this->projector->onUserConfirmed($event);

        // Assert
        $this->assertDatabaseHas(User::class, [
            'uuid' => $event->userUuid,
            'email' => $event->email,
            'email_verified_at' => $event->createdAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
