<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Cookie\CookieJar;
use Illuminate\Routing\Router;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JMac\Testing\Traits\AdditionalAssertions;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Livewire\Login;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserPostLoginDataCreated;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class LoginTest extends TestCase
{
    use AdditionalAssertions;

    private Hasher $hasher;

    private StatefulGuard $guard;

    private CookieJar $cookieJar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = $this->app->make(Hasher::class);
        $this->guard = $this->app->make(Guard::class);
        $this->session = $this->app->make(Store::class);
        $this->cookieJar = $this->app->make(CookieJar::class);

        $this->app->make(Router::class)->get('/test', fn () => 'Welcome')->name('test');
        $this->app->make(Repository::class)->set('auditable-users.redirect_after_login', 'test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_login(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        tap(User::factory()->make([
            'email' => 'admin2@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        // Act
        $this->assertNull($this->guard->user());
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', 'password')
            ->call('login');

        // Assert
        $livewire->assertHasNoErrors();
        $livewire->assertRedirectToRoute('test');

        $this->assertTrue($this->guard->user()->uuid->equals($user->uuid));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_to_login_with_the_wrong_password(): void
    {
        // Arrange
        tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        // Act
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', '123')
            ->call('login');

        // Assert
        $this->assertTrue($this->guard->guest());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function session_regenerates(): void
    {
        // Arrange
        tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        $this->session->setId($preLogin = Str::random(40));

        // Act
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', 'password')
            ->call('login');

        // Assert
        $this->assertNotEquals($preLogin, $this->session->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rate_limits_logins_to_3(): void
    {
        // Arrange
        tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        $fiftyNineSecondsFromNow = CarbonImmutable::now()->addSeconds(59);

        // Act & Assert
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', 'password');

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $livewire->call('login');
        $this->assertGuest();

        $this->travelTo($fiftyNineSecondsFromNow);

        $livewire->call('login');
        $this->assertGuest();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rate_limit_resets_after_a_minute(): void
    {
        // Arrange
        tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        $sixtyOneSecondsFromNow = CarbonImmutable::now()->addSeconds(61);

        // Act & Assert
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', 'password');

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $livewire->call('login');
        $this->assertAuthenticated();
        $this->guard->logout();

        $this->travelTo($sixtyOneSecondsFromNow);

        $livewire->call('login');
        $this->assertAuthenticated();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function remember_me_functionality_works(): void
    {
        // Arrange
        $user = tap(User::factory()->make([
            'email' => 'admin@auditable-users.com',
            'password' => $this->hasher->make('password'),
        ])->writeable())->save();

        // Act
        $this->assertNull($this->guard->user());
        $livewire = Livewire::test(Login::class)
            ->set('email', 'admin@auditable-users.com')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('login');

        // Assert
        $targetEvent = Collection::make(UserAggregateRoot::retrieve($user->uuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserPostLoginDataCreated);

        [$userUuidFromCookie, $token] = explode('|', $this->cookieJar->getQueuedCookies()[0]->getValue());

        $this->assertTrue($targetEvent->userUuid->equals(Uuid::fromString($userUuidFromCookie)));
        $this->assertSame($targetEvent->rememberToken, $token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function expected_middlewares_are_used(): void
    {
        $this->assertRouteUsesMiddleware('login', [
            RedirectIfAuthenticated::class,
        ]);
    }
}
