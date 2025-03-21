<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-auditable-users::info-card />
        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Reset your password</flux:heading>
            </div>

            <form wire:submit.prevent="sendEmail">
                <div class="space-y-6">
                    <flux:field>
                        <flux:input wire:model="email" label="Email" type="email" placeholder="Your email address" />
                    </flux:field>
                </div>

                <flux:button size="sm" type="submit" class="w-full mt-5">Reset</flux:button>
            </form>

            <flux:separator text="or" />

            <flux:button wire:navigate.hover href="{{ route('login') }}" size="sm" variant="ghost" class="w-full">Login to your account</flux:button>
        </flux:card>
    </div>
</div>
