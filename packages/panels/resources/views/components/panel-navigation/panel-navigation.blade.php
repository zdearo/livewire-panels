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
                    <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
                        {{ $navigationItem->displayLabel() }}
                    </flux:sidebar.item>
                @endforeach

                @foreach($navigationGroups as $navigationGroup)
                    <flux:sidebar.group expandable :icon="$navigationGroup->icon" heading="{{ $navigationGroup->displayLabel() }}" class="grid">
                        @foreach($navigationGroup->items as $navigationItem)
                            <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            {!! $this->mobileHeaderEnd() !!}
        </flux:header>
    @else
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700" data-livewire-panels-topbar>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

            {!! $this->topbarBrand() !!}

            <flux:navbar class="-mb-px max-lg:hidden">
                @foreach($navigationItems as $navigationItem)
                    <flux:navbar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
                        {{ $navigationItem->displayLabel() }}
                    </flux:navbar.item>
                @endforeach

                @if($navigationItems !== [] && $navigationGroups !== [])
                    <flux:separator vertical variant="subtle" class="my-2" />
                @endif

                @foreach($navigationGroups as $navigationGroup)
                    @if($mode->value === 'topbar')
                        <flux:dropdown hover class="max-lg:hidden" data-livewire-panels-navigation-dropdown>
                            <flux:navbar.item :icon="$navigationGroup->icon" icon:trailing="chevron-down">
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
                            :icon="$navigationGroup->icon"
                            :href="$this->groupUrl($navigationGroup)"
                            :current="$activeGroup?->id === $navigationGroup->id"
                        >
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
                    <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
                        {{ $navigationItem->displayLabel() }}
                    </flux:sidebar.item>
                @endforeach

                @foreach($navigationGroups as $navigationGroup)
                    <flux:sidebar.group expandable :icon="$navigationGroup->icon" heading="{{ $navigationGroup->displayLabel() }}" class="grid">
                        @foreach($navigationGroup->items as $navigationItem)
                            <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$this->navigationItemIsCurrent($navigationItem)">
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
