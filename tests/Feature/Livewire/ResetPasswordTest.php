<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Livewire;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Livewire\ResetPassword;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPasswordReset;
use XbNz\LaravelAuditableUsers\Tests\FlushRedis;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class ResetPasswordTest extends TestCase
{
    use FlushRedis;
    use WithFaker;

    private PasswordResetTokenRepository $passwordResetTokenRepository;

    private Hasher $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordResetTokenRepository = $this->app->make(PasswordResetTokenRepository::class);
        $this->hasher = $this->app->make(Hasher::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resets_a_password_if_the_token_is_valid(): void
    {
        // Arrange
        $user = tap(User::factory()->make()->writeable())->save();
        $this->passwordResetTokenRepository->create($user->uuid, 'test token');

        // Act
        $livewire = Livewire::withQueryParams(['token' => urlencode($this->hasher->make('test token'))])
            ->test(ResetPassword::class, ['userUuid' => $user->uuid->toString()])
            ->set('newPassword', $this->faker->password(minLength: 20))
            ->call('resetPassword');

        // Assert
        $livewire->assertOk();

        $targetEvent = Collection::make(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserPasswordReset);

        $this->assertTrue($user->uuid->equals($targetEvent->userUuid));
        $this->assertTrue($this->hasher->check($livewire->newPassword, $targetEvent->hashedNewPassword));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function if_the_token_is_invalid_the_password_is_not_reset(): void
    {
        // Arrange
        $user = tap(User::factory()->make()->writeable())->save();
        $this->passwordResetTokenRepository->create($user->uuid, 'test token');

        // Act
        $livewire = Livewire::withQueryParams(['token' => urlencode($this->hasher->make('invalid token'))])
            ->test(ResetPassword::class, ['userUuid' => $user->uuid->toString()])
            ->set('newPassword', $this->faker->password(minLength: 20))
            ->call('resetPassword');

        // Assert
        $this->assertEmpty(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function properties_are_locked(): void
    {
        // Arrange
        $user = tap(User::factory()->make()->writeable())->save();
        $locked = [
            'userUuid',
            'email',
            'token',
        ];

        // Act

        foreach ($locked as $property) {
            try {
                Livewire::withQueryParams(['token' => 'token'])
                    ->test(ResetPassword::class, ['userUuid' => $user->uuid->toString()])
                    ->set($property, 'value');
            } catch (CannotUpdateLockedPropertyException) {
                // Assert
                $this->assertTrue(true);

                continue;
            }

            $this->fail("The {$property} property should be locked.");
        }
    }

    protected function redisConnectionsToFlush(): array
    {
        return ['default'];
    }
}
