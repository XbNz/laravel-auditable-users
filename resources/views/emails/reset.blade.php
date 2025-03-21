@component('mail::message')
# Someone requested a password reset for your  {{ config('app.name') }} account.

If this was you, click the button below to reset your password. If you didn't request a password reset, you can safely ignore this email.

@component('mail::button', ['url' => $viewModel->resetUrl])
Reset Password
@endcomponent

Thanks,<br>
The  {{ config('app.name') }} Team
@endcomponent
