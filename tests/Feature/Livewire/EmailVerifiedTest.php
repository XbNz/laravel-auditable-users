<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Commands\CommandBus;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Commands\Confirm;
use XbNz\LaravelAuditableUsers\Commands\Register;
use XbNz\LaravelAuditableUsers\Livewire\EmailVerified;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\StoredEvents\UserConfirmed;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class EmailVerifiedTest extends TestCase
{
    use AdditionalAssertions;

    private UrlGenerator $urlGenerator;

    private CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->app->make(UrlGenerator::class);
        $this->commandBus = $this->app->make(CommandBus::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_confirms_an_email(): void
    {
        // Arrange
        $userUuid = Uuid::uuid7();
        $this->commandBus->dispatch(
            new Register(
                $userUuid,
                'admin@auditable-users.com',
                'password',
            )
        );

        $signedUrl = $this->urlGenerator->signedRoute(
            'confirmEmail',
            [
                'userUuid' => $userUuid->toString(),
                'email' => 'admin@auditable-users.com',
            ],
        );

        // Act
        $liveWire = Livewire::withQueryParams([
            'email' => 'admin@auditable-users.com',
        ])->test(EmailVerified::class, [
            'userUuid' => $userUuid->toString(),
        ]);

        // Assert
        $liveWire->assertOk();
        $targetEvent = Collection::make(UserAggregateRoot::retrieve($userUuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserConfirmed);

        $this->assertTrue($userUuid->equals($targetEvent->userUuid));
        $this->assertSame('admin@auditable-users.com', $targetEvent->email);

        $this->assertDatabaseHas(User::class, [
            'uuid' => $userUuid->toString(),
            'email' => 'admin@auditable-users.com',
            'email_verified_at' => $targetEvent->createdAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_an_error_if_the_email_has_already_been_confirmed(): void
    {
        // Arrange
        $this->withoutMiddleware(ValidateSignature::class);
        $userUuid = Uuid::uuid7();
        $this->commandBus->dispatch(
            new Register(
                $userUuid,
                'admin@auditable-users.com',
                'password',
            )
        );

        $time = CarbonImmutable::now();

        $this->commandBus->dispatch(
            new Confirm(
                $userUuid,
                'admin@auditable-users.com',
            )
        );

        $this->travelTo($time->addMinutes(1));

        // Act

        $livewire = Livewire::withQueryParams([
            'email' => 'admiN@auditable-users.com',
        ])->test(EmailVerified::class, [
            'userUuid' => $userUuid->toString(),
        ]);

        // Assert
        $livewire->assertHasErrors('email', 'Email already verified');
        $livewire->assertSee('Email already verified');

        $this->assertDatabaseHas(User::class, [
            'uuid' => $userUuid->toString(),
            'email' => 'admin@auditable-users.com',
            'email_verified_at' => $time->format('Y-m-d H:i:s'),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function expected_middlewares_are_used(): void
    {
        $this->assertRouteUsesMiddleware('confirmEmail', [
            ValidateSignature::class,
        ]);
    }
}
