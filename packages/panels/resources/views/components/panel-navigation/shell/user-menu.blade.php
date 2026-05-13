@if($variant === 'sidebar')
    <flux:dropdown position="top" align="start" class="max-lg:hidden" data-livewire-panels-user-menu>
        <flux:sidebar.profile :name="$userName" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $userName }}</flux:menu.radio>
            </flux:menu.radio.group>
        </flux:menu>
    </flux:dropdown>
@else
    <flux:dropdown position="top" align="end" data-livewire-panels-user-menu>
        <flux:profile :name="$userName" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $userName }}</flux:menu.radio>
            </flux:menu.radio.group>
        </flux:menu>
    </flux:dropdown>
@endif
