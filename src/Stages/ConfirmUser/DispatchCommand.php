<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ConfirmUser;

use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\Confirm;

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

        $this->commandBus->dispatch(new Confirm(
            $payload->userUuid,
            $payload->email,
        ));

        return $payload;
    }
}
