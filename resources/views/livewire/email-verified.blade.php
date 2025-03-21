<div>
    <div class="flex flex-col">
        <flux:card>
            <div class="flex flex-col space-y-4">
                <div class="flex items-center space-x-4">
                    @svg('heroicon-o-check-circle', 'h-6 w-6 text-green-500')
                    <flux:heading size="lg">Email Verified</flux:heading>
                </div>

                <flux:subheading>
                    Thanks for verifying
                    <flux:badge size="sm">{{ $email }}</flux:badge>
                </flux:subheading>
            </div>

            <flux:error name="email" />
        </flux:card>

        <div class="mt-4 w-full">
            <a href="{{ route('login') }}" wire:navigate.hover>
                <flux:button class="w-full">
                    Continue to Login
                </flux:button>
            </a>
        </div>
    </div>
</div>
