<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Mailables;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\ViewModels\ConfirmationEmailViewModel;

final class ConfirmationMail extends Mailable
{
    public function __construct(
        private readonly ConfirmationEmailViewModel $viewModel,
    ) {}

    public function envelope(): Envelope
    {
        $appName = config('app.name');

        Assert::string($appName);

        return new Envelope(
            subject: "{$appName} - Confirm your email address",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auditable-users::emails.confirmation',
            with: ['viewModel' => $this->viewModel],
        );
    }
}
