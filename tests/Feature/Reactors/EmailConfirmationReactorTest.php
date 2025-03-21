<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\Reactors;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Projectionist;
use XbNz\LaravelAuditableUsers\Mailables\ConfirmationMail;
use XbNz\LaravelAuditableUsers\Reactors\EmailConfirmationReactor;
use XbNz\LaravelAuditableUsers\StoredEvents\UserRegistered;
use XbNz\LaravelAuditableUsers\Tests\TestCase;

final class EmailConfirmationReactorTest extends TestCase
{
    private UrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->app->make(UrlGenerator::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_dispatches_an_email_with_the_correct_signed_url(): void
    {
        // Arrange
        Mail::fake();

        $reactor = $this->app->make(EmailConfirmationReactor::class);

        $event = new UserRegistered(
            Uuid::uuid7(),
            'admin@auditable-users.com',
            'hashed-password',
        );

        $expectedSignedUrl = $this->urlGenerator->signedRoute(
            'confirmEmail',
            [
                'email' => $event->email,
                'userUuid' => $event->userUuid,
            ],
        );

        // Act
        $reactor->onUserRegistered($event);

        // Assert
        Mail::assertSentCount(1);
        Mail::assertSent(function (ConfirmationMail $mail) use ($event, $expectedSignedUrl) {
            return $mail->hasTo($event->email)
                && invade($mail)->viewModel->confirmationUrl === $expectedSignedUrl;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reactor_is_registered(): void
    {
        $this->app->make(Projectionist::class)->getReactors()
            ->sole(fn (object $reactor) => $reactor instanceof EmailConfirmationReactor);

        $this->assertTrue(true);
    }
}
