<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Enums;

enum ConfirmationState: string
{
    case Unverified = 'unverified';
    case Verified = 'verified';
}
