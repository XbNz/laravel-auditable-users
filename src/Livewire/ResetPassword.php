<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Livewire;

use Carbon\CarbonInterval;
use Flux\FluxManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use League\Pipeline\Pipeline;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Contracts\PasswordResetTokenRepository;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Stages\ResetPassword\DispatchCommand;
use XbNz\LaravelAuditableUsers\Stages\ResetPassword\HashPassword;
use XbNz\LaravelAuditableUsers\Stages\ResetPassword\Transporter;

final class ResetPassword extends Component
{
    private PasswordResetTokenRepository $passwordResetTokenRepository;

    private Hasher $hasher;

    private Pipeline $pipeline;

    private FluxManager $flux;

    private DispatchCommand $dispatchCommand;

    private HashPassword $hashPassword;

    #[Locked]
    public string $email = '';

    #[Locked]
    public string $userUuid = '';

    #[Locked]
    public string $token = '';

    public string $newPassword = '';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'newPassword' => [
                'required',
                Password::min(8)->uncompromised(),
            ],
        ];
    }

    public function resetPassword(): void
    {
        $this->validate();

        $hasMatching = $this->passwordResetTokenRepository->all(Uuid::fromString($this->userUuid))
            ->filter(fn (string $token) => $this->hasher->check($token, $this->token))
            ->isNotEmpty();

        if ($hasMatching === false) {
            $this->flux->toast(
                text: 'Password reset expired. Please request a new one.',
                duration: CarbonInterval::seconds(5)->totalMilliseconds,
                variant: 'danger'
            );

            $this->redirectAction(ForgotPassword::class, navigate: true);

            return;
        }

        $this->pipeline
            ->pipe($this->hashPassword)
            ->pipe($this->dispatchCommand)
            ->process(new Transporter(
                Uuid::fromString($this->userUuid),
                $this->newPassword,
            ));

        $this->flux->toast(
            text: 'Password has been reset.',
            duration: CarbonInterval::seconds(5)->totalMilliseconds,
            variant: 'success'
        );

        $this->redirectAction(Login::class, navigate: true);
    }

    public function render(): View
    {
        return view('auditable-users::livewire.reset-password');
    }

    public function mount(string $userUuid, Request $request): void
    {
        $user = User::query()->findOrFail($userUuid);
        $unEncodedToken = $request->query('token');

        Assert::string($unEncodedToken);

        $this->email = $user->email;
        $this->userUuid = $userUuid;
        $this->token = urldecode($unEncodedToken);
    }

    public function boot(): void
    {
        $this->passwordResetTokenRepository = Container::getInstance()->make(PasswordResetTokenRepository::class);
        $this->hasher = Container::getInstance()->make(Hasher::class);
        $this->flux = Container::getInstance()->make(FluxManager::class);
        $this->pipeline = Container::getInstance()->make(Pipeline::class);
        $this->dispatchCommand = Container::getInstance()->make(DispatchCommand::class);
        $this->hashPassword = Container::getInstance()->make(HashPassword::class);
    }
}
