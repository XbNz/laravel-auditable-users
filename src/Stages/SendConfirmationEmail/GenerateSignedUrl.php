<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendConfirmationEmail;

use Illuminate\Contracts\Routing\UrlGenerator;
use League\Pipeline\StageInterface;
use Webmozart\Assert\Assert;

final class GenerateSignedUrl implements StageInterface
{
    public function __construct(
        private readonly UrlGenerator $urlGenerator,
    ) {}

    /**
     * @param  Transporter  $payload
     */
    public function __invoke(mixed $payload): mixed
    {
        Assert::isInstanceOf($payload, Transporter::class);

        $payload->signedConfirmationUrl = $this->urlGenerator->signedRoute(
            'confirmEmail',
            [
                'userUuid' => $payload->userUuid,
                'email' => $payload->email,
            ],
        );

        return $payload;
    }
}
