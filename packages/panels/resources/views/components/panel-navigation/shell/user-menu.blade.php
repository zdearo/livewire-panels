@if($variant === 'sidebar')
    <flux:dropdown position="top" align="start" class="max-lg:hidden" data-livewire-panels-user-menu>
        <flux:sidebar.profile :name="$userName" />

        <flux:menu>
            <div class="px-2 py-1.5" data-livewire-panels-user-menu-identity>
                <div class="truncate text-sm font-medium text-zinc-800 dark:text-white">{{ $userName }}</div>

                @if($userEmail !== null)
                    <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $userEmail }}</div>
                @endif
            </div>

            @if($logoutUrl !== null)
                <flux:menu.separator />

                <form method="POST" action="{{ $logoutUrl }}">
                    @csrf

                    <flux:menu.item as="button" type="submit">
                        <x-slot name="icon">
                            <x-icon name="heroicon-o-arrow-right-start-on-rectangle" class="me-2" data-flux-menu-item-icon />
                        </x-slot>

                        {{ __('Logout') }}
                    </flux:menu.item>
                </form>
            @endif
        </flux:menu>
    </flux:dropdown>
@else
    <flux:dropdown position="top" align="end" data-livewire-panels-user-menu>
        <flux:profile :name="$userName" />

        <flux:menu>
            <div class="px-2 py-1.5" data-livewire-panels-user-menu-identity>
                <div class="truncate text-sm font-medium text-zinc-800 dark:text-white">{{ $userName }}</div>

                @if($userEmail !== null)
                    <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $userEmail }}</div>
                @endif
            </div>

            @if($logoutUrl !== null)
                <flux:menu.separator />

                <form method="POST" action="{{ $logoutUrl }}">
                    @csrf

                    <flux:menu.item as="button" type="submit">
                        <x-slot name="icon">
                            <x-icon name="heroicon-o-arrow-right-start-on-rectangle" class="me-2" data-flux-menu-item-icon />
                        </x-slot>

                        {{ __('Logout') }}
                    </flux:menu.item>
                </form>
            @endif
        </flux:menu>
    </flux:dropdown>
@endif
