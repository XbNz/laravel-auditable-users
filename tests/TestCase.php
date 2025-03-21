<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Tests;

use Dotenv\Dotenv;
use Flux\FluxServiceProvider;
use FluxPro\FluxProServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\EventSourcing\EventSourcingServiceProvider;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Providers\AuditableUsersServiceProvider;

class TestCase extends OrchestraTestCase
{
    use LazilyRefreshDatabase;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dotEnv = Dotenv::createImmutable(__DIR__.'/../', '.env.testing');
        $dotEnv->load();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AuditableUsersServiceProvider::class,
            EventSourcingServiceProvider::class,
            LivewireServiceProvider::class,
            FluxServiceProvider::class,
            FluxProServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->make(Kernel::class)->call('vendor:publish', ['--tag' => 'event-sourcing-migrations']);
        $app->make(Repository::class)->set('auth.providers.users.model', User::class);
    }
}
