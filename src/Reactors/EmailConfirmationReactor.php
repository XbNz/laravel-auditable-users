<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Reactors;

use Illuminate\Contracts\Queue\ShouldQueue;
use League\Pipeline\Pipeline;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail\GenerateSignedUrl;
use XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail\SendConfirmationEmail;
use XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail\Transporter;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;

final class EmailConfirmationReactor extends Reactor implements ShouldQueue
{
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly GenerateSignedUrl $generateSignedUrl,
        private readonly SendConfirmationEmail $sendConfirmationEmail,
    ) {}

    public function onUserRegistered(UserRegistered $event): void
    {
        $this->pipeline
            ->pipe($this->generateSignedUrl)
            ->pipe($this->sendConfirmationEmail)
            ->process(new Transporter(
                $event->userUuid,
                $event->email,
            ));
    }
}
