<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use XbNz\LaravelAuditableUsers\Projections\User;

final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => null,
        ];
    }
}
