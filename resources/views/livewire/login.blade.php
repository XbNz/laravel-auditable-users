<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-auditable-users::info-card />
        
        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Login to your account</flux:heading>
            </div>

            <form wire:submit.prevent="login">
                <div class="space-y-6">
                    <flux:input wire:model="email" label="Email" type="email" placeholder="Your email address" />

                    <flux:input wire:model="password" label="Password" type="password" placeholder="Your password" />

                    <flux:checkbox wire:model="remember" label="Remember me" />
                </div>

                <flux:button size="sm" type="submit" class="w-full mt-5">Login</flux:button>
            </form>

            <flux:separator text="or" />

            <div class="flex gap-3">
                <flux:button wire:navigate.hover href="{{ route('register') }}" size="sm" variant="ghost" class="w-full">Register</flux:button>
                <flux:button wire:navigate.hover href="{{ route('forgotPassword') }}" size="sm" variant="ghost" class="w-full">Forgot Password</flux:button>
            </div>

        </flux:card>
    </div>
</div>
