<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @implements CastsAttributes<UuidInterface, mixed>
 */
final class UuidCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?UuidInterface
    {
        if ($value === null) {
            return null;
        }

        Assert::uuid($value);

        return Uuid::fromString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (is_string($value) === true) {
            Assert::uuid($value);

            return $value;
        }

        Assert::isInstanceOf($value, UuidInterface::class);

        return $value->toString();
    }
}
