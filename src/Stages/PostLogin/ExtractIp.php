<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\PostLogin;

use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;

final class ExtractIp implements StageInterface
{
    /**
     * @param  Transporter  $payload
     */
    public function __invoke($payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);

        $payload->ipAddress = $payload->request->ip();

        return $payload;
    }
}
