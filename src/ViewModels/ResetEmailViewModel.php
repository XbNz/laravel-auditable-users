<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\ViewModels;

final class ResetEmailViewModel
{
    public function __construct(
        public readonly string $resetUrl
    ) {}
}
