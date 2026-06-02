<section data-livewire-panels-navigation>
    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="#"
                logo="https://fluxui.dev/img/demo/logo.png"
                logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
                name="Acme Inc."
            />

            <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @foreach($this->navigationContract()?->items() ?? [] as $navigationItem)
                <flux:sidebar.item
                    :wire:navigate="$this->navigationItemUsesSpa($navigationItem)"
                    :badge="$navigationItem->displayBadge()"
                    :href="$navigationItem->displayUrl() ?? '#'"
                    :current="$this->navigationItemIsCurrent($navigationItem)"
                >
                    @if($navigationItem->icon !== null && $navigationItem->icon !== '')
                        <x-slot name="icon">
                            <x-icon
                                :name="$navigationItem->icon"
                                class="size-4 in-data-flux-sidebar-group-dropdown:text-zinc-400! dark:in-data-flux-sidebar-group-dropdown:text-white/80! [[data-flux-sidebar-item]:hover_&]:text-current!"
                            />
                        </x-slot>
                    @endif

                    {{ $navigationItem->displayLabel() }}
                </flux:sidebar.item>
            @endforeach

            @foreach($this->navigationContract()?->groups() ?? [] as $navigationGroup)
                <flux:sidebar.group expandable heading="{{ $navigationGroup->displayLabel() }}" class="grid">
                    @if($navigationGroup->icon !== null && $navigationGroup->icon !== '')
                        <x-slot name="icon">
                            <x-icon
                                :name="$navigationGroup->icon"
                                class="size-4 in-data-flux-menu:text-zinc-400 in-data-flux-menu:dark:text-white/80 in-data-flux-menu:[[data-flux-sidebar-group-dropdown]>button:hover_&]:text-current"
                            />
                        </x-slot>
                    @endif

                    @foreach($navigationGroup->items as $navigationItem)
                        <flux:sidebar.item
                            :wire:navigate="$this->navigationItemUsesSpa($navigationItem)"
                            :badge="$navigationItem->displayBadge()"
                            :href="$navigationItem->displayUrl() ?? '#'"
                            :current="$this->navigationItemIsCurrent($navigationItem)"
                        >
                            @if($navigationItem->icon !== null && $navigationItem->icon !== '')
                                <x-slot name="icon">
                                    <x-icon
                                        :name="$navigationItem->icon"
                                        class="size-4 in-data-flux-sidebar-group-dropdown:text-zinc-400! dark:in-data-flux-sidebar-group-dropdown:text-white/80! [[data-flux-sidebar-item]:hover_&]:text-current!"
                                    />
                                </x-slot>
                            @endif

                            {{ $navigationItem->displayLabel() }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @endforeach
        </flux:sidebar.nav>

        <flux:sidebar.spacer />
    </flux:sidebar>
</section>
