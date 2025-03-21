<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Reactors;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\Commands\CommandBus;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use XbNz\LaravelAuditableUsers\Commands\ClearResetTokens;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;

final class PasswordResetReactor extends Reactor implements ShouldQueue
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    public function onUserPasswordReset(UserPasswordReset $event): void
    {
        $this->commandBus->dispatch(new ClearResetTokens(
            $event->userUuid,
        ));
    }
}
