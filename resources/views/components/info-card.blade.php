<div class="hidden flex-col justify-center md:flex gap-10">
    <div>
        <flux:heading size="xl">Welcome to {{ config('app.name') }}</flux:heading>
        <flux:subheading>Get started with our services</flux:subheading>
    </div>

    <div class="flex flex-col gap-3">
         <span class="flex gap-3">
            @svg('heroicon-o-academic-cap', 'h-5 w-5')
            <flux:description>Feature 1</flux:description>
        </span>
        <span class="flex gap-3">
            @svg('heroicon-o-archive-box', 'h-5 w-5')
            <flux:description>Feature 2</flux:description>
        </span>
        <span class="flex gap-3">
            @svg('heroicon-o-light-bulb', 'h-5 w-5')
            <flux:description>Feature 3</flux:description>
        </span>
    </div>
</div>