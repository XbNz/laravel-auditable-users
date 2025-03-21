<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendResetEmail;

use Illuminate\Contracts\Mail\Mailer;
use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Mailables\ResetMail;
use XbNz\LaravelAuditableUsers\ViewModels\ResetEmailViewModel;

final class SendResetEmail implements StageInterface
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
        Assert::notNull($payload->signedResetUrl);

        $this->mailer
            ->to($payload->email)
            ->send(new ResetMail(new ResetEmailViewModel($payload->signedResetUrl)));

        return $payload;
    }
}
