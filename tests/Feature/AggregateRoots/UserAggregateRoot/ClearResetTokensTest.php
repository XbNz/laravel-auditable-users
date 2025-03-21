<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests\Feature\AggregateRoots\UserAggregateRoot;

use Ramsey\Uuid\Uuid;
use XbNz\LaravelAuditableUsers\Commands\ClearResetTokens;
use XbNz\LaravelAuditableUsers\StoredEvents\UserResetTokensCleared;
use XbNz\LaravelAuditableUsers\Tests\TestCase;
use XbNz\LaravelAuditableUsers\UserAggregateRoot;

final class ClearResetTokensTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function clear_reset_tokens(): void
    {
        $userUuid = Uuid::uuid7();

        UserAggregateRoot::fake($userUuid->toString())
            ->given([
            ])
            ->when(function (UserAggregateRoot $user) use ($userUuid): void {
                $user->clearResetTokens(new ClearResetTokens(
                    $userUuid,
                ));
            })
            ->assertRecorded(
                new UserResetTokensCleared(
                    $userUuid,
                )
            );
    }
}
