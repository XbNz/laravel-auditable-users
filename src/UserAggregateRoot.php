<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Commands\ClearResetTokens;
use XbNz\LaravelAuditableUsers\Commands\Confirm;
use XbNz\LaravelAuditableUsers\Commands\CreatePostLoginData;
use XbNz\LaravelAuditableUsers\Commands\CreateResetToken;
use XbNz\LaravelAuditableUsers\Commands\Register;
use XbNz\LaravelAuditableUsers\Commands\ResetPassword;
use XbNz\LaravelAuditableUsers\Commands\SendResetEmail;
use XbNz\LaravelAuditableUsers\Enums\ConfirmationState;
use XbNz\LaravelAuditableUsers\StoredEvents\UserConfirmed;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPostLoginDataCreated;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetEmailSent;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokenCreated;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokensCleared;

final class UserAggregateRoot extends AggregateRoot
{
    public function __construct(
        private ConfirmationState $confirmationState = ConfirmationState::Unverified
    ) {}

    public function register(Register $command): self
    {
        $this->recordThat(
            new UserRegistered(
                $command->userUuid,
                $command->email,
                $command->hashedPassword,
            )
        );

        return $this;
    }

    public function confirm(Confirm $command): void
    {
        Assert::eq($this->confirmationState, ConfirmationState::Unverified);

        $this->recordThat(
            new UserConfirmed(
                $command->userUuid,
                $command->email,
            )
        );
    }

    public function createPostLoginData(CreatePostLoginData $command): void
    {
        $this->recordThat(
            new UserPostLoginDataCreated(
                $command->userUuid,
                $command->userAgent,
                $command->ipAddress,
                $command->email,
                $command->rememberToken,
            )
        );
    }

    public function sendResetEmail(SendResetEmail $command): void
    {
        $this->recordThat(
            new UserResetEmailSent(
                $command->userUuid,
                $command->email,
            )
        );
    }

    public function createResetToken(CreateResetToken $command): void
    {
        $this->recordThat(
            new UserResetTokenCreated(
                $command->userUuid,
                $command->plainTextToken,
            )
        );
    }

    public function resetPassword(ResetPassword $command): void
    {
        $this->recordThat(
            new UserPasswordReset(
                $command->userUuid,
                $command->hashedNewPassword
            )
        );
    }

    public function clearResetTokens(ClearResetTokens $command): void
    {
        $this->recordThat(
            new UserResetTokensCleared($command->userUuid)
        );
    }

    public function applyConfirm(UserConfirmed $event): void
    {
        $this->confirmationState = ConfirmationState::Verified;
    }
}
