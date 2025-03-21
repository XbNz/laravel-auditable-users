<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Projectors;

use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserConfirmed;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPostLoginDataCreated;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokenCreated;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokensCleared;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Webmozart\Assert\Assert;

final class UserProjector extends Projector
{
    public function __construct(
        private readonly PasswordResetTokenRepository $passwordResetTokenRepository,
    ) {
    }

    public function onUserRegistered(UserRegistered $event): void
    {
        Assert::notNull($event->createdAt());

        new User()->writeable()->create([
            'uuid' => $event->userUuid,
            'email' => $event->email,
            'password' => $event->hashedPassword,
            'created_at' => $event->createdAt()->toImmutable(),
        ]);
    }

    public function onUserConfirmed(UserConfirmed $event): void
    {
        Assert::notNull($event->createdAt());
        
        User::query()->findOrFail($event->userUuid)
            ->writeable()
            ->update([
                'email_verified_at' => $event->createdAt()->format('Y-m-d H:i:s'),
            ]);
    }

    public function onUserResetTokenCreated(UserResetTokenCreated $event): void
    {
        $this->passwordResetTokenRepository->create(
            $event->userUuid,
            $event->plainTextToken,
        );
    }

    public function onUserPasswordReset(UserPasswordReset $event): void
    {
        User::query()->findOrFail($event->userUuid)
            ->writeable()
            ->update([
                'password' => $event->hashedNewPassword,
            ]);
    }

    public function onUserResetTokensCleared(UserResetTokensCleared $event): void
    {
        $this->passwordResetTokenRepository->deleteAll($event->userUuid);
    }

    public function onUserPostLoginDataCreated(UserPostLoginDataCreated $event): void
    {
        User::query()->findOrFail($event->userUuid)
            ->writeable()
            ->update([
                'remember_token' => $event->rememberToken,
            ]);
    }
}
