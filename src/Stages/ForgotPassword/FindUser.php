<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ForgotPassword;

use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Projections\User;

final class FindUser implements StageInterface
{
    /**
     * @param  Transporter  $payload
     */
    public function __invoke(mixed $payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);

        $payload->userUuid = User::query()
            ->where('email', $payload->email)
            ->sole()
            ->uuid;

        return $payload;
    }
}
