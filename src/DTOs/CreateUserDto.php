<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\DTOs;

use SensitiveParameter;

final class CreateUserDto
{
    public function __construct(
        public readonly string $email,
        #[SensitiveParameter] public readonly string $password,
    ) {}
}
