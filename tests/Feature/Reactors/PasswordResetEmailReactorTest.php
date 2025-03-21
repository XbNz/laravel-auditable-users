<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Reactors;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use XbNz\LaravelAuditableUsers\Mailables\ResetMail;
use XbNz\LaravelAuditableUsers\Reactors\PasswordResetEmailReactor;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetEmailSent;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokenCreated;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class PasswordResetEmailReactorTest extends TestCase
{
    private UrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->app->make(UrlGenerator::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_dispatches_an_email_with_the_correct_signed_url_and_reset_token(): void
    {
        // Arrange
        Mail::fake();

        $hasherFake = $this->mock(Hasher::class);
        $hasherFake->shouldReceive('make')->once()->andReturn('hashed token');

        $reactor = $this->app->make(PasswordResetEmailReactor::class);

        $event = new UserResetEmailSent(
            Uuid::uuid7(),
            'admin@auditable-users.com',
        );

        $expectedSignedUrl = $this->urlGenerator->temporarySignedRoute(
            'resetPassword',
            CarbonImmutable::now()->addMinutes(30),
            [
                'userUuid' => $event->userUuid,
                'token' => urlencode('hashed token'),
            ],
        );

        // Act
        $reactor->onUserResetEmailSent($event);

        // Assert
        $targetEvent = Collection::make(UserAggregateRoot::retrieve($event->userUuid->toString())->getAppliedEvents())
            ->sole(fn (ShouldBeStored $event) => $event instanceof UserResetTokenCreated);

        $this->assertTrue($event->userUuid->equals($targetEvent->userUuid));
        Mail::assertSentCount(1);
        Mail::assertSent(function (ResetMail $mail) use ($event, $expectedSignedUrl) {
            return $mail->hasTo($event->email)
                && invade($mail)->viewModel->resetUrl === $expectedSignedUrl;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reactor_is_registered(): void
    {
        $this->app->make(Projectionist::class)->getReactors()
            ->sole(fn (object $reactor) => $reactor instanceof PasswordResetEmailReactor);

        $this->assertTrue(true);
    }
}
