<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\RegisterUser;

use XbNz\LaravelAuditableUsers\DTOs\CreateUserDto;

final class Transporter
{
    public function __construct(
        public readonly CreateUserDto $createUserDto,
        public ?string $hashedPassword = null,
    ) {}
}
