<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail;

use Illuminate\Contracts\Mail\Mailer;
use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Mailables\ConfirmationMail;
use XbNz\LaravelAuditableUsers\ViewModels\ConfirmationEmailViewModel;

final class SendConfirmationEmail implements StageInterface
{
    public function __construct(
        private readonly Mailer $mailer
    ) {}

    /**
     * @param  Transporter  $payload
     */
    public function __invoke(mixed $payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);
        Assert::notNull($payload->signedConfirmationUrl);

        $this->mailer
            ->to($payload->email)
            ->send(new ConfirmationMail(new ConfirmationEmailViewModel($payload->signedConfirmationUrl)));

        return $payload;
    }
}
