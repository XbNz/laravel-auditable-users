<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ResetPassword;

use Illuminate\Contracts\Hashing\Hasher;
use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;

final class HashPassword implements StageInterface
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {}

    /**
     * @param  Transporter  $payload
     */
    public function __invoke($payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);

        $payload->hashedPassword = $this->hasher->make($payload->newPassword);

        return $payload;
    }
}
