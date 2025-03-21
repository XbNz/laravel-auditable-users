<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendResetEmail;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use League\Pipeline\StageInterface;
use Spatie\EventSourcing\Commands\CommandBus;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\CreateResetToken;

final class GenerateResetToken implements StageInterface
{
    public function __construct(
        private readonly Hasher $hasher,
        private readonly CommandBus $commandBus,
    ) {}

    /**
     * @param  Transporter  $payload
     */
    public function __invoke(mixed $payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);

        $payload->hashedResetToken = $this->hasher->make($randomString = Str::random(40));

        $this->commandBus->dispatch(new CreateResetToken(
            $payload->userUuid,
            $randomString,
        ));

        return $payload;
    }
}
