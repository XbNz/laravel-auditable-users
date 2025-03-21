<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Commands;

use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\HandledBy;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

#[HandledBy(UserAggregateRoot::class)]
final class ResetPassword
{
    public function __construct(
        #[AggregateUuid] public readonly UuidInterface $userUuid,
        public readonly string $hashedNewPassword,
    ) {}
}
