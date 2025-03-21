<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\ResetPassword;

use League\Pipeline\StageInterface;
use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\ResetPassword;

final class DispatchCommand implements StageInterface
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
        Assert::notNull($payload->hashedPassword);

        $this->commandBus->dispatch(new ResetPassword(
            $payload->userUuid,
            $payload->hashedPassword,
        ));

        return $payload;
    }
}
