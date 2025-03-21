<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests;

use Illuminate\Redis\RedisManager;
use Webmozart\Assert\Assert;

trait FlushRedis
{
    /**
     * @return array<int, string>
     */
    abstract protected function redisConnectionsToFlush(): array;

    protected function setUpFlushRedis(): void
    {
        $name = $this->redisConnectionsToFlush();

        Assert::isList($name);

        $this->afterApplicationCreated(function () use ($name): void {
            foreach ($name as $connection) {
                $this->app->make(RedisManager::class)->connection($connection)->client()->flushDB();
            }
        });
    }
}
