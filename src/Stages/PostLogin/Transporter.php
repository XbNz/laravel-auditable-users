<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\PostLogin;

use Illuminate\Http\Request;
use Ramsey\Uuid\UuidInterface;

final class Transporter
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        public readonly Request $request,
        public readonly string $email,
        public readonly ?string $rememberToken = null,
        public ?string $userAgent = null,
        public ?string $ipAddress = null,
    ) {}
}
