<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Livewire;

use Carbon\CarbonInterval;
use Flux\FluxManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;
use League\Pipeline\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Component;
use XbNz\LaravelAuditableUsers\DTOs\CreateUserDto;
use XbNz\LaravelAuditableUsers\Enums\CacheKeys;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Stages\RegisterUser\DispatchCommand;
use XbNz\LaravelAuditableUsers\Stages\RegisterUser\HashPassword;
use XbNz\LaravelAuditableUsers\Stages\RegisterUser\Transporter;

#[Layout('components.layouts.app')]
final class Register extends Component
{
    private Pipeline $pipeline;

    private DispatchCommand $dispatchCommandStage;

    private HashPassword $hashPasswordStage;

    private FluxManager $flux;

    private RateLimiter $rateLimiter;

    public string $email = '';

    public string $password = '';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                new Unique(User::class, 'email'),
            ],
            'password' => [
                'required',
                Password::min(8)->uncompromised(),
            ],
        ];
    }

    public function register(): void
    {
        if ($this->rateLimiter->tooManyAttempts(CacheKeys::RegisterRateLimiter->value, 3) === true) {
            $this->flux->toast(
                text: 'Too many registration attempts.',
                duration: CarbonInterval::seconds(5)->totalMilliseconds,
                variant: 'danger'
            );

            return;
        }

        $this->validate();

        $transporter = new Transporter(
            new CreateUserDto(
                $this->email,
                $this->password
            )
        );

        $this->pipeline
            ->pipe($this->hashPasswordStage)
            ->pipe($this->dispatchCommandStage)
            ->process($transporter);

        $this->rateLimiter->increment(CacheKeys::RegisterRateLimiter->value, (int) CarbonInterval::days(1)->totalSeconds);

        $this->flux->toast(
            'Confirmation email has been sent to your email address.',
            'Confirmation email sent',
            CarbonInterval::seconds(5)->totalMilliseconds,
            'success'
        );
    }

    public function render(): View
    {
        return view('auditable-users::livewire.register');
    }

    public function boot(): void
    {
        $this->pipeline = Container::getInstance()->make(Pipeline::class);
        $this->hashPasswordStage = Container::getInstance()->make(HashPassword::class);
        $this->dispatchCommandStage = Container::getInstance()->make(DispatchCommand::class);
        $this->flux = Container::getInstance()->make(FluxManager::class);
        $this->rateLimiter = Container::getInstance()->make(RateLimiter::class);
    }
}
