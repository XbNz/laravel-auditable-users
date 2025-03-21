<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Projectors\UserProjector;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokenCreated;
use XbNz\LaravelAuditableUsers\Tests\FlushRedis;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class OnUserResetTokenCreatedTest extends TestCase
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
    public function user_reset_token_created(): void
    {
        // Arrange
        $event = new UserResetTokenCreated(
            $uuid = Uuid::uuid7(),
            $token = $this->faker->password(),
        );

        $this->storedEventRepository->persist($event, $uuid->toString());

        // Act
        $this->projector->onUserResetTokenCreated($event);

        // Assert
        $savedToken = $this->passwordResetTokenRepository->all($uuid)->sole();
        $this->assertSame($token, $savedToken);
    }
}
