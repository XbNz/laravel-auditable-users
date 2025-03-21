<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Livewire\ForgotPassword;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetEmailSent;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class ForgotPasswordTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_forgot_password_signed_url_through_email(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => 'password',
        ])->writeable())->save();

        // Act
        $livewire = Livewire::test(ForgotPassword::class)
            ->set('email', 'admin@auditable-users.com')
            ->call('sendEmail');

        // Assert
        $livewire->assertHasNoErrors();

        $targetEvent = Collection::make(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserResetEmailSent);

        $this->assertSame('admin@auditable-users.com', $targetEvent->email);
        $this->assertTrue($user->uuid->equals($targetEvent->userUuid));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rate_limits_to_3(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => 'password',
        ])->writeable())->save();

        // Act
        $livewire = Livewire::test(ForgotPassword::class)
            ->set('email', 'admin@auditable-users.com');

        $livewire->call('sendEmail');
        $livewire->call('sendEmail');
        $livewire->call('sendEmail');
        $livewire->call('sendEmail');

        // Assert
        $targetEvents = Collection::make(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents())
            ->filter(fn (ShouldBeStored $event) => $event instanceof UserResetEmailSent);

        $this->assertCount(3, $targetEvents);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function if_a_non_existent_email_is_given_we_hit_em_with_a_bit_of_security_through_obscurity(): void
    {
        // Arrange
        // Act
        $livewire = Livewire::test(ForgotPassword::class)
            ->set('email', 'nonexistent@auditable-users.com')
            ->call('sendEmail');

        // Assert
        $livewire->assertHasNoErrors();
        $livewire->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rate_limit_resets_after_an_hour(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => 'password',
        ])->writeable())->save();

        $fiftyNineMinutesFromNow = CarbonImmutable::now()->addMinutes(59);
        $sixtyOneMinutesFromNow = CarbonImmutable::now()->addMinutes(61);

        // Act & Assert
        $livewire = Livewire::test(ForgotPassword::class)
            ->set('email', 'admin@auditable-users.com');

        $livewire->call('sendEmail');
        $livewire->call('sendEmail');
        $livewire->call('sendEmail');
        $this->travelTo($fiftyNineMinutesFromNow);
        $livewire->call('sendEmail');

        $this->assertCount(3, $this->targetEvents($user->uuid));

        $this->travelTo($sixtyOneMinutesFromNow);

        $livewire->call('sendEmail');

        $this->assertCount(4, $this->targetEvents($user->uuid));
    }

    private function targetEvents(UuidInterface $uuid): Collection
    {
        return Collection::make(UserAggregateRoot::retrieve($uuid->toString())->getAppliedEvents())
            ->filter(fn (ShouldBeStored $event) => $event instanceof UserResetEmailSent);
    }
}
