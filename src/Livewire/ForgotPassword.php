<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Livewire;

use Carbon\CarbonInterval;
use Flux\FluxManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use League\Pipeline\Pipeline;
use Livewire\Component;
use Throwable;
use XbNz\LaravelAuditableUsers\Enums\CacheKeys;
use XbNz\LaravelAuditableUsers\Stages\ForgotPassword\DispatchCommand;
use XbNz\LaravelAuditableUsers\Stages\ForgotPassword\FindUser;
use XbNz\LaravelAuditableUsers\Stages\ForgotPassword\Transporter;

final class ForgotPassword extends Component
{
    private FluxManager $flux;

    private RateLimiter $rateLimiter;

    private Pipeline $pipeline;

    private FindUser $findUser;

    private DispatchCommand $dispatchCommand;

    public string $email = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function sendEmail(): void
    {
        $this->validate();

        if ($this->rateLimiter->tooManyAttempts(CacheKeys::ForgotPasswordRateLimiter->value, 3) === true) {
            $this->flux->toast(
                text: 'Too many forgot password attempts.',
                duration: CarbonInterval::seconds(5)->totalMilliseconds,
                variant: 'danger'
            );

            return;
        }

        $this->rateLimiter->increment(CacheKeys::ForgotPasswordRateLimiter->value, (int) CarbonInterval::hours(1)->totalSeconds);

        try {
            $this->pipeline
                ->pipe($this->findUser)
                ->pipe($this->dispatchCommand)
                ->process(new Transporter($this->email));
        } catch (Throwable) {
        } finally {
            $this->flux->toast(
                text: 'An email has been sent to you with instructions on how to reset your password.',
                duration: CarbonInterval::seconds(5)->totalMilliseconds,
                variant: 'success'
            );
        }
    }

    public function render(): View
    {
        return view('auditable-users::livewire.forgot-password');
    }

    public function boot(): void
    {
        $this->flux = Container::getInstance()->make(FluxManager::class);
        $this->rateLimiter = Container::getInstance()->make(RateLimiter::class);
        $this->pipeline = Container::getInstance()->make(Pipeline::class);

        $this->findUser = Container::getInstance()->make(FindUser::class);
        $this->dispatchCommand = Container::getInstance()->make(DispatchCommand::class);
    }
}
