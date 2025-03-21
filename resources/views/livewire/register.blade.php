<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-auditable-users::info-card />
        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Register for an account</flux:heading>
                <flux:subheading>Sign up for access to our services</flux:subheading>
            </div>

            <form wire:submit.prevent="register">
                <div class="space-y-6">
                    <flux:field>
                        <flux:input wire:model="email" label="Email" type="email" placeholder="Your email address" />
                    </flux:field>

                    <flux:field>
                        <flux:input wire:model="password" label="Password" type="password" placeholder="Your password" />
                    </flux:field>
                </div>

                <flux:button size="sm" type="submit" class="w-full mt-5">Register</flux:button>
            </form>

            <flux:separator text="or" />

            <flux:button wire:navigate.hover href="{{ route('login') }}" size="sm" variant="ghost" class="w-full">Login to your account</flux:button>
        </flux:card>
    </div>
</div>
