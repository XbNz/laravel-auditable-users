<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Repositories;

use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;
use Redis;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;

final class RedisPasswordResetTokenRepository implements PasswordResetTokenRepository
{
    public function __construct(private readonly Redis $redis) {}

    public function create(UuidInterface $userUuid, string $plainTextToken): void
    {
        $this->redis->sAdd("password_reset_tokens:{$userUuid->toString()}", $plainTextToken);
    }

    public function all(UuidInterface $userUuid): Collection
    {
        return Collection::make($this->redis->sMembers("password_reset_tokens:{$userUuid->toString()}"));
    }

    public function delete(UuidInterface $userUuid, string $plainTextToken): void
    {
        $this->redis->sRem("password_reset_tokens:{$userUuid->toString()}", $plainTextToken);
    }

    public function deleteAll(UuidInterface $userUuid): void
    {
        $this->redis->del("password_reset_tokens:{$userUuid->toString()}");
    }
}
