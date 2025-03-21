<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Stages\SendResetEmail;

use Carbon\CarbonImmutable;
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
        Assert::notNull($payload->hashedResetToken);

        $payload->signedResetUrl = $this->urlGenerator->temporarySignedRoute(
            'resetPassword',
            CarbonImmutable::now()->addMinutes(30),
            [
                'userUuid' => $payload->userUuid,
                'token' => urlencode($payload->hashedResetToken),
            ],
        );

        return $payload;
    }
}
