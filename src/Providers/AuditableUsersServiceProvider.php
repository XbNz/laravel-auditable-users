<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Providers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\EventSourcing\Projectionist;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Livewire\EmailVerified;
use XbNz\LaravelAuditableUsers\Livewire\ForgotPassword;
use XbNz\LaravelAuditableUsers\Livewire\Login;
use XbNz\LaravelAuditableUsers\Livewire\Register;
use XbNz\LaravelAuditableUsers\Livewire\ResetPassword;
use XbNz\LaravelAuditableUsers\Normalizers\RamseyUuidNormalizer;
use XbNz\LaravelAuditableUsers\Projectors\UserProjector;
use XbNz\LaravelAuditableUsers\Reactors\EmailConfirmationReactor;
use XbNz\LaravelAuditableUsers\Reactors\PasswordResetEmailReactor;
use XbNz\LaravelAuditableUsers\Reactors\PasswordResetReactor;
use XbNz\LaravelAuditableUsers\Repositories\RedisPasswordResetTokenRepository;

final class AuditableUsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RedisPasswordResetTokenRepository::class, function (Application $foundation) {
            $redis = $foundation->make(RedisManager::class)
                ->connection('default')
                ->client();

            return new RedisPasswordResetTokenRepository($redis);
        });

        $this->app->singleton(PasswordResetTokenRepository::class, RedisPasswordResetTokenRepository::class);

        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'auditable-users');
    }

    public function boot(): void
    {
        $this->app->make(Projectionist::class)
            ->addProjectors([
                UserProjector::class,
            ])
            ->addReactors([
                EmailConfirmationReactor::class,
                PasswordResetEmailReactor::class,
                PasswordResetReactor::class,
            ]);

        Livewire::component('auditable-users::login', Login::class);
        Livewire::component('auditable-users::register', Register::class);
        Livewire::component('auditable-users::forgot-password', ForgotPassword::class);
        Livewire::component('auditable-users::reset-password', ResetPassword::class);
        Livewire::component('auditable-users::email-verified', EmailVerified::class);

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'auditable-users');
        $this->registerRoutes();

        if ($this->app->runningInConsole() === true) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('auditable-users.php'),
            ], 'auditable-users-config');

            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/auditable-users'),
            ], 'auditable-users-views');

            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'event-sourcing-migrations');
        }

        $this->app->make(Repository::class)->set(
            'event-sourcing.event_normalizers',
            array_merge(
                [
                    RamseyUuidNormalizer::class,
                ],
                $this->app->make(Repository::class)->get('event-sourcing.event_normalizers'),
            )
        );
    }

    private function registerRoutes(): void
    {
        $this->app->make(Router::class)
            ->group(
                [
                    'middleware' => 'web',
                ],
                fn () => $this->loadRoutesFrom(__DIR__.'/../../routes/web.php')
            );
    }
}
