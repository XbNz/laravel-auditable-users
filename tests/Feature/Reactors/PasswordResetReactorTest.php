<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Reactors;

use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Reactors\PasswordResetReactor;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokensCleared;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class PasswordResetReactorTest extends TestCase
{
    private PasswordResetReactor $reactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reactor = $this->app->make(PasswordResetReactor::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_clears_reset_tokens(): void
    {
        // Arrange
        $event = new UserPasswordReset(
            Uuid::uuid7(),
            'password',
        );

        // Act
        $this->reactor->onUserPasswordReset($event);

        // Assert
        $targetEvent = Collection::make(UserAggregateRoot::retrieve($event->userUuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserResetTokensCleared);

        $this->assertTrue($event->userUuid->equals($targetEvent->userUuid));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reactor_is_registered(): void
    {
        $this->app->make(Projectionist::class)->getReactors()
            ->sole(fn (object $reactor) => $reactor instanceof PasswordResetReactor);

        $this->assertTrue(true);
    }
}
