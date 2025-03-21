<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Enums;

enum CacheKeys: string
{
    case LoginRateLimiter = 'login_rate_limiter';
    case RegisterRateLimiter = 'register_rate_limiter';
    case ForgotPasswordRateLimiter = 'forgot_password_rate_limiter';
}
