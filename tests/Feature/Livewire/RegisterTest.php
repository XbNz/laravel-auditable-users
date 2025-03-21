<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Livewire\Livewire;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Livewire\Register;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class RegisterTest extends TestCase
{
    use AdditionalAssertions;
    use WithFaker;

    private Hasher $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->app->make(Hasher::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_user(): void
    {
        // Arrange
        $password = $this->faker->password(15);

        // Act
        $this->assertDatabaseCount(User::class, 0);
        $livewire = Livewire::test(Register::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        // Assert
        $livewire->assertHasNoErrors();
        $user = User::query()->sole();

        $targetEvent = Collection::make(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserRegistered);

        $this->assertSame('admin@auditable-users.com', $targetEvent->email);
        $this->assertTrue($this->hasher->check($password, $targetEvent->hashedPassword));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rate_limits_registration_to_3_per_day(): void
    {
        // Arrange
        $password = $this->faker->password(15);
        $twentyThreeHoursFromNow = CarbonImmutable::now()->addHours(23);
        $twentyFiveHoursFromNow = CarbonImmutable::now()->addHours(25);

        // Act
        $this->assertDatabaseCount(User::class, 0);

        Livewire::test(Register::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        Livewire::test(Register::class)
            ->set('email', 'admin2@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        Livewire::test(Register::class)
            ->set('email', 'admin3@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        Livewire::test(Register::class)
            ->set('email', 'admin4@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        $this->travelTo($twentyThreeHoursFromNow);

        Livewire::test(Register::class)
            ->set('email', 'admin5@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        $this->travelTo($twentyFiveHoursFromNow);

        Livewire::test(Register::class)
            ->set('email', 'admin6@auditable-users.com')
            ->set('password', $password)
            ->call('register');

        // Assert
        $this->assertDatabaseCount(User::class, 4);
        $this->assertDatabaseMissing(User::class, ['email' => 'admin5@auditable-users.com']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function expected_middlewares_are_used(): void
    {
        $this->assertRouteUsesMiddleware('register', [
            RedirectIfAuthenticated::class,
        ]);
    }
}
