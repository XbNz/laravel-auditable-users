@component('mail::message')
# Please confirm your email address

Thanks for creating an account with  {{ config('app.name') }}. Please click the button below to confirm your email address.

@component('mail::button', ['url' => $viewModel->confirmationUrl])
Confirm Email
@endcomponent

Thanks,<br>
The {{ config('app.name') }} Team
@endcomponent
