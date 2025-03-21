<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Reactors;

use Illuminate\Contracts\Queue\ShouldQueue;
use League\Pipeline\Pipeline;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use XbNz\LaravelAuditableUsers\Stages\SendResetEmail\GenerateResetToken;
use XbNz\LaravelAuditableUsers\Stages\SendResetEmail\GenerateSignedUrl;
use XbNz\LaravelAuditableUsers\Stages\SendResetEmail\SendResetEmail;
use XbNz\LaravelAuditableUsers\Stages\SendResetEmail\Transporter;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetEmailSent;

final class PasswordResetEmailReactor extends Reactor implements ShouldQueue
{
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly GenerateSignedUrl $generateSignedUrl,
        private readonly GenerateResetToken $generateResetToken,
        private readonly SendResetEmail $sendResetEmail,
    ) {}

    public function onUserResetEmailSent(UserResetEmailSent $event): void
    {
        $this->pipeline
            ->pipe($this->generateResetToken)
            ->pipe($this->generateSignedUrl)
            ->pipe($this->sendResetEmail)
            ->process(new Transporter(
                $event->userUuid,
                $event->email,
            ));
    }
}
