<?php

declare(strict_types=1);

namespace XbNz\LaravelAuditableUsers\Livewire;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use League\Pipeline\Pipeline;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;
use XbNz\LaravelAuditableUsers\Projections\User;
use XbNz\LaravelAuditableUsers\Stages\ConfirmUser\DispatchCommand;
use XbNz\LaravelAuditableUsers\Stages\ConfirmUser\Transporter;

final class EmailVerified extends Component
{
    public string $email = '';

    public function render(): View
    {
        return view('auditable-users::livewire.email-verified', [
            'email' => $this->email,
        ]);
    }

    public function mount(
        string $userUuid,
    ): void {
        $request = Container::getInstance()->make(Request::class);
        $pipeline = Container::getInstance()->make(Pipeline::class);
        $dispatchCommand = Container::getInstance()->make(DispatchCommand::class);

        Assert::string($email = $request->query('email'));

        $this->email = $email;

        $user = User::query()->findOrFail($userUuid);

        if ($user->email_verified_at !== null) {
            $this->addError('email', 'Email already verified');

            return;
        }

        $pipeline
            ->pipe($dispatchCommand)
            ->process(new Transporter(Uuid::fromString($userUuid), $email));
    }
}
