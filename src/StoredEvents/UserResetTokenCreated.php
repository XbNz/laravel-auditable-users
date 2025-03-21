<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\StoredEvents;

use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class UserResetTokenCreated extends ShouldBeStored
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        public readonly string $plainTextToken,
    ) {}
}
