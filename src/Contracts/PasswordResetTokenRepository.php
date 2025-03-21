<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Contracts;

use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface PasswordResetTokenRepository
{
    public function create(UuidInterface $userUuid, string $plainTextToken): void;

    /**
     * @return Collection<int, string>
     */
    public function all(UuidInterface $userUuid): Collection;

    public function delete(UuidInterface $userUuid, string $plainTextToken): void;

    public function deleteAll(UuidInterface $userUuid): void;
}
