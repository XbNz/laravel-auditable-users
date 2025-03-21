<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ForgotPassword;

use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\SendResetEmail;

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
        Assert::notNull($payload->userUuid);

        $this->commandBus->dispatch(new SendResetEmail(
            $payload->userUuid,
            $payload->email,
        ));

        return $payload;
    }
}
