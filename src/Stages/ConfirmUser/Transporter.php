<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ConfirmUser;

use Ramsey\Uuid\UuidInterface;

final class Transporter
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        public readonly string $email,
    ) {}
}
