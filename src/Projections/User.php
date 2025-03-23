<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Projections;

use DateTimeImmutable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\Projections\Projection;
use XbNz\LaravelAuditableUsers\Casts\UuidCast;
use XbNz\LaravelAuditableUsers\Database\Factories\UserFactory;

/**
 * @property UuidInterface $uuid
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property DateTimeImmutable $created_at
 * @property DateTimeImmutable $updated_at
 */
final class User extends Projection implements Authenticatable
{
    use AuthenticatableTrait;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'uuid' => UuidCast::class,
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
