<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail;

use Ramsey\Uuid\UuidInterface;

final class Transporter
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        public readonly string $email,
        public ?string $signedConfirmationUrl = null,
    ) {}
}
