<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Mailables;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\ViewModels\ResetEmailViewModel;

final class ResetMail extends Mailable
{
    public function __construct(
        private readonly ResetEmailViewModel $viewModel,
    ) {}

    public function envelope(): Envelope
    {
        $appName = config('app.name');

        Assert::string($appName);

        return new Envelope(
            subject: "{$appName} - Reset your email address",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auditable-users::emails.reset',
            with: ['viewModel' => $this->viewModel],
        );
    }
}
