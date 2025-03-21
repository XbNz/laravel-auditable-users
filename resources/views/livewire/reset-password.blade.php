<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-auditable-users::info-card />

        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Reset your password</flux:heading>
            </div>

            <form wire:submit.prevent="resetPassword">
                <div class="space-y-6">
                    <flux:input readonly label="Email" type="email" value="{{ $email }}" />
                    <flux:input wire:model="newPassword" label="New Password" type="password" placeholder="Your new password" />
                </div>

                <flux:button size="sm" type="submit" class="w-full mt-5">Reset</flux:button>
            </form>

            <flux:separator text="or" />

            <flux:button wire:navigate.hover href="{{ route('login') }}" size="sm" variant="ghost" class="w-full">Login to your account</flux:button>
        </flux:card>
    </div>
</div>
