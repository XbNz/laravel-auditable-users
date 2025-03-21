<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Livewire;

use Carbon\CarbonInterval;
use Flux\FluxManager;
use Illuminate\Auth\SessionGuard;
use Illuminate\Cache\RateLimiter;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Str;
use League\Pipeline\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Enums\CacheKeys;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Stages\PostLogin\DispatchCommand;
use XbNz\LaravelAuditableUsers\Stages\PostLogin\ExtractIp;
use XbNz\LaravelAuditableUsers\Stages\PostLogin\ExtractUserAgent;
use XbNz\LaravelAuditableUsers\Stages\PostLogin\Transporter;

final class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    private SessionGuard $guard;

    private FluxManager $flux;

    private RateLimiter $rateLimiter;

    private Store $session;

    private Pipeline $pipeline;

    private Request $request;

    private ExtractIp $extractIp;

    private ExtractUserAgent $extractUserAgent;

    private Repository $config;

    private DispatchCommand $dispatchCommand;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ];
    }

    public function login(): void
    {
        if ($this->rateLimiter->tooManyAttempts(CacheKeys::LoginRateLimiter->value, 3) === true) {
            $this->flux->toast(
                text: 'Too many login attempts.',
                duration: CarbonInterval::seconds(5)->totalMilliseconds,
                variant: 'danger'
            );

            return;
        }

        $this->rateLimiter->increment(CacheKeys::LoginRateLimiter->value, (int) CarbonInterval::seconds(60)->totalSeconds);

        $this->validate();

        $user = $this->guard->getProvider()->retrieveByCredentials($credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        Assert::isInstanceOf($user, User::class);

        $valid = $this->guard->getProvider()->validateCredentials($user, $credentials);

        if ($valid === false) {
            $this->flux->toast(
                'The given credentials were invalid.',
                'Invalid credentials',
                CarbonInterval::seconds(5)->totalMilliseconds,
                'danger'
            );

            return;
        }

        if ($this->remember === true) {
            $user->setRememberToken($token = Str::random(60));
        }

        $this->guard->login($user, $this->remember);

        $this->session->regenerate();

        $this->pipeline
            ->pipe($this->extractIp)
            ->pipe($this->extractUserAgent)
            ->pipe($this->dispatchCommand)
            ->process(new Transporter(
                $user->uuid,
                $this->request,
                $this->email,
                $token ?? null
            ));

        $this->redirectRoute(
            name: $this->config->get('auditable-users.redirect_after_login'),
            navigate: true
        );
    }

    public function render(): View
    {
        return view('auditable-users::livewire.login');
    }

    public function boot(): void
    {
        $this->guard = Container::getInstance()->make(Guard::class);
        $this->flux = Container::getInstance()->make(FluxManager::class);
        $this->rateLimiter = Container::getInstance()->make(RateLimiter::class);
        $this->session = Container::getInstance()->make(Store::class);
        $this->pipeline = Container::getInstance()->make(Pipeline::class);
        $this->request = Container::getInstance()->make(Request::class);
        $this->extractIp = Container::getInstance()->make(ExtractIp::class);
        $this->extractUserAgent = Container::getInstance()->make(ExtractUserAgent::class);
        $this->dispatchCommand = Container::getInstance()->make(DispatchCommand::class);
        $this->config = Container::getInstance()->make(Repository::class);
    }
}
