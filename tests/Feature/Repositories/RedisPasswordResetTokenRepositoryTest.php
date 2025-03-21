<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Repositories\RedisPasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Tests\FlushRedis;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class RedisPasswordResetTokenRepositoryTest extends TestCase
{
    use FlushRedis;
    use WithFaker;

    private readonly RedisPasswordResetTokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(RedisPasswordResetTokenRepository::class);
    }

    protected function redisConnectionsToFlush(): array
    {
        return ['default'];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_a_new_reset_token(): void
    {
        // Act
        $this->repository->create($uuid = Uuid::uuid7(), $value = $this->faker->password());

        // Assert
        $this->assertSame($value, $this->repository->all($uuid)->sole());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_a_reset_token(): void
    {
        // Arrange
        $this->repository->create($uuid = Uuid::uuid7(), $value = $this->faker->password());

        // Act
        $this->assertNotEmpty($this->repository->all($uuid));
        $this->repository->delete($uuid, $value);

        // Assert
        $this->assertEmpty($this->repository->all($uuid));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_all_reset_tokens(): void
    {
        // Arrange
        $this->repository->create($uuid = Uuid::uuid7(), $value = $this->faker->password());

        // Act
        $this->assertNotEmpty($this->repository->all($uuid));
        $this->repository->deleteAll($uuid);

        // Assert
        $this->assertEmpty($this->repository->all($uuid));
    }
}
