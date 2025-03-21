<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ResetPassword;

use Ramsey\Uuid\UuidInterface;
use SensitiveParameter;

final class Transporter
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        #[SensitiveParameter] public readonly string $newPassword,
        public ?string $hashedPassword = null,
    ) {}
}
