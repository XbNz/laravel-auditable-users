<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ForgotPassword;

use Ramsey\Uuid\UuidInterface;

final class Transporter
{
    public function __construct(
        public readonly string $email,
        public ?UuidInterface $userUuid = null,
    ) {}
}
