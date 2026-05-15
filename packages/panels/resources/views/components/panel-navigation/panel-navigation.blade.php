@php
    $mode = $this->navigationMode();
    $navigationItems = $this->navigationItems();
    $navigationGroups = $this->navigationGroups();
    $activeGroup = $this->activeGroup();
@endphp

<section
    data-livewire-panels-navigation
    data-livewire-panels-navigation-mode="{{ $mode->value }}"
    @if($activeGroup !== null) data-livewire-panels-active-group="{{ $activeGroup->id }}" @endif
>
    @if($mode->value === 'sidebar')
        <flux:sidebar
            sticky
            collapsible
            class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700"
            x-on:click.capture="if ($event.target === $el && $el.hasAttribute('data-flux-sidebar-collapsed-desktop')) $event.stopImmediatePropagation()"
            data-livewire-panels-primary-sidebar
        >
            <flux:sidebar.header>
                {!! $this->sidebarBrand() !!}

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @foreach($navigationItems as $navigationItem)
                    <flux:sidebar.item :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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

                @foreach($navigationGroups as $navigationGroup)
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
                            <flux:sidebar.item :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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

            {!! $this->sidebarFooter() !!}
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:button
                class="lg:hidden shrink-0"
                variant="subtle"
                square
                x-data
                x-on:click="$dispatch('flux-sidebar-toggle')"
                aria-label="{{ __('Toggle sidebar') }}"
                data-flux-sidebar-toggle
                inset="left"
            >
                <x-slot name="icon">
                    <x-icon name="heroicon-o-bars-2" class="size-5" />
                </x-slot>
            </flux:button>

            <flux:spacer />

            {!! $this->mobileHeaderEnd() !!}
        </flux:header>
    @else
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700" data-livewire-panels-topbar>
            <flux:button
                class="lg:hidden shrink-0"
                variant="subtle"
                square
                x-data
                x-on:click="$dispatch('flux-sidebar-toggle')"
                aria-label="{{ __('Toggle sidebar') }}"
                data-flux-sidebar-toggle
            >
                <x-slot name="icon">
                    <x-icon name="heroicon-o-bars-2" class="size-5" />
                </x-slot>
            </flux:button>

            {!! $this->topbarBrand() !!}

            <flux:navbar class="-mb-px max-lg:hidden">
                @foreach($navigationItems as $navigationItem)
                    <flux:navbar.item :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
                        @if($navigationItem->icon !== null && $navigationItem->icon !== '')
                            <x-slot name="icon">
                                <x-icon :name="$navigationItem->icon" class="size-5" />
                            </x-slot>
                        @endif

                        {{ $navigationItem->displayLabel() }}
                    </flux:navbar.item>
                @endforeach

                @if($navigationItems !== [] && $navigationGroups !== [])
                    <flux:separator vertical variant="subtle" class="my-2" />
                @endif

                @foreach($navigationGroups as $navigationGroup)
                    @if($mode->value === 'topbar')
                        <flux:dropdown hover class="max-lg:hidden" data-livewire-panels-navigation-dropdown>
                            <flux:navbar.item>
                                @if($navigationGroup->icon !== null && $navigationGroup->icon !== '')
                                    <x-slot name="icon">
                                        <x-icon :name="$navigationGroup->icon" class="size-5" />
                                    </x-slot>
                                @endif

                                <x-slot name="iconTrailing">
                                    <x-icon name="heroicon-o-chevron-down" class="size-4 ms-1" />
                                </x-slot>

                                {{ $navigationGroup->displayLabel() }}
                            </flux:navbar.item>

                            <flux:navmenu>
                                @foreach($navigationGroup->items as $navigationItem)
                                    <flux:navmenu.item :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
                                        {{ $navigationItem->displayLabel() }}
                                    </flux:navmenu.item>
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                    @else
                        <flux:navbar.item
                            :href="$this->groupUrl($navigationGroup)"
                            :current="$activeGroup?->id === $navigationGroup->id"
                        >
                            @if($navigationGroup->icon !== null && $navigationGroup->icon !== '')
                                <x-slot name="icon">
                                    <x-icon :name="$navigationGroup->icon" class="size-5" />
                                </x-slot>
                            @endif

                            {{ $navigationGroup->displayLabel() }}
                        </flux:navbar.item>
                    @endif
                @endforeach
            </flux:navbar>

            <flux:spacer />

            {!! $this->topbarEnd() !!}
        </flux:header>

        <flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700" data-livewire-panels-mobile-sidebar>
            <flux:sidebar.header>
                {!! $this->mobileSidebarBrand() !!}

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @foreach($navigationItems as $navigationItem)
                    <flux:sidebar.item :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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

                @foreach($navigationGroups as $navigationGroup)
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
                            <flux:sidebar.item :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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
    @endif
</section>
