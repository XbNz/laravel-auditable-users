<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokensCleared;
use XbNz\LaravelAuditableUsers\Tests\FlushRedis;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserResetTokensClearedTest extends TestCase
{
    use FlushRedis;
    use WithFaker;

    private UserProjector $projector;

    private StoredEventRepository $storedEventRepository;

    private PasswordResetTokenRepository $passwordResetTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projector = $this->app->make(UserProjector::class);
        $this->storedEventRepository = $this->app->make(StoredEventRepository::class);
        $this->passwordResetTokenRepository = $this->app->make(PasswordResetTokenRepository::class);
    }

    protected function redisConnectionsToFlush(): array
    {
        return ['default'];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_reset_tokens_are_cleared(): void
    {
        // Arrange
        $uuid = Uuid::uuid7();
        $this->passwordResetTokenRepository->create($uuid, $this->faker->password());

        $event = new UserResetTokensCleared($uuid);

        $this->storedEventRepository->persist($event, $uuid->toString());

        // Act
        $this->projector->onUserResetTokensCleared($event);

        // Assert
        $this->assertEmpty($this->passwordResetTokenRepository->all($uuid));
    }
}
