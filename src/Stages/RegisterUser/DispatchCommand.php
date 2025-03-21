<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\RegisterUser;

use League\Pipeline\StageInterface;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\Register;

final class DispatchCommand implements StageInterface
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke($payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);
        Assert::notNull($payload->hashedPassword);

        $this->commandBus->dispatch(new Register(
            Uuid::uuid7(),
            $payload->createUserDto->email,
            $payload->hashedPassword,
        ));

        return $payload;
    }
}
