<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Commands;

use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\HandledBy;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

#[HandledBy(UserAggregateRoot::class)]
final class CreatePostLoginData
{
    public function __construct(
        #[AggregateUuid] public readonly UuidInterface $userUuid,
        public readonly string $userAgent,
        public readonly string $ipAddress,
        public readonly string $email,
        public readonly ?string $rememberToken = null,
    ) {}
}
