<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\PostLogin;

use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\CreatePostLoginData;

final class DispatchCommand
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    /**
     * @param  Transporter  $payload
     */
    public function __invoke($payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);
        Assert::notNull($payload->userAgent);
        Assert::notNull($payload->ipAddress);

        $this->commandBus->dispatch(new CreatePostLoginData(
            $payload->userUuid,
            $payload->userAgent,
            $payload->ipAddress,
            $payload->email,
            $payload->rememberToken,
        ));

        return $payload;
    }
}
