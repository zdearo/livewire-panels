<flux:sidebar.brand
    href="{{ $brandUrl }}"
    name="{{ $panel->displayName() }}"
>
    <flux:avatar size="xs" :name="$panel->displayName()" />
</flux:sidebar.brand>
