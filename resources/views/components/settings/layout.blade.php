<div class="flex items-start max-md:flex-col px-10">
    <div class="me-10 w-full pb-4 md:w-[220px] hidden md:block">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.avatar')" wire:navigate>{{ __('Avatar') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>
    <div class="me-10 w-full pb-4 md:w-[220px] md:hidden">
        <flux:navbar class="flex justify-between">
            <flux:navbar.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navbar.item>
            <flux:navbar.item :href="route('settings.avatar')" wire:navigate>{{ __('Avatar') }}</flux:navbar.item>
            <flux:navbar.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navbar.item>
            <flux:navbar.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navbar.item>
        </flux:navbar>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>

        
    </div>
</div>
