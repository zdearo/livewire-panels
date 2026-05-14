@php
    $mode = $this->navigationMode();
    $navigationItems = $this->navigationItems();
    $navigationGroups = $this->navigationGroups();
    $activeGroup = $this->activeGroup();
@endphp

<section data-livewire-panels-navigation data-livewire-panels-navigation-mode="{{ $mode->value }}">
    @if($mode->value === 'sidebar')
        <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700" data-livewire-panels-primary-sidebar>
            <flux:sidebar.header>
                {!! $this->sidebarBrand() !!}

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @foreach($navigationItems as $navigationItem)
                    <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
                        {{ $navigationItem->displayLabel() }}
                    </flux:sidebar.item>
                @endforeach

                @foreach($navigationGroups as $navigationGroup)
                    <flux:sidebar.group expandable :icon="$navigationGroup->icon" heading="{{ $navigationGroup->displayLabel() }}" class="grid">
                        @foreach($navigationGroup->items as $navigationItem)
                            <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
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

        <flux:main data-livewire-panels-content>
            {{ $slot }}
        </flux:main>
    @else
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700" data-livewire-panels-topbar>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

            {!! $this->topbarBrand() !!}

            <flux:navbar class="-mb-px max-lg:hidden">
                @foreach($navigationItems as $navigationItem)
                    <flux:navbar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
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
                                    <flux:navmenu.item :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
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
                    <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
                        {{ $navigationItem->displayLabel() }}
                    </flux:sidebar.item>
                @endforeach

                @foreach($navigationGroups as $navigationGroup)
                    <flux:sidebar.group expandable :icon="$navigationGroup->icon" heading="{{ $navigationGroup->displayLabel() }}" class="grid">
                        @foreach($navigationGroup->items as $navigationItem)
                            <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->displayBadge()" :href="$navigationItem->displayUrl() ?? '#'" :current="$navigationItem->isCurrent()">
                                {{ $navigationItem->displayLabel() }}
                            </flux:sidebar.item>
                        @endforeach
                    </flux:sidebar.group>
                @endforeach
            </flux:sidebar.nav>

            <flux:sidebar.spacer />
        </flux:sidebar>

        <flux:main container data-livewire-panels-content>
            @if($mode->value === 'topbar-sidebar')
                <div class="flex max-md:flex-col items-start">
                    @if($activeGroup !== null)
                        <div class="w-full md:w-[220px] pb-4 me-10" data-livewire-panels-secondary-navigation data-livewire-panels-active-group="{{ $activeGroup->id }}">
                            <flux:navlist>
                                @foreach($activeGroup->items as $navigationItem)
                                    <flux:navlist.item :href="$navigationItem->displayUrl() ?? '#'" :badge="$navigationItem->displayBadge()" :current="$navigationItem->isCurrent()">
                                        {{ $navigationItem->displayLabel() }}
                                    </flux:navlist.item>
                                @endforeach
                            </flux:navlist>
                        </div>

                        <flux:separator class="md:hidden" />
                    @endif

                    <div class="flex-1 max-md:pt-6 self-stretch">
                        {{ $slot }}
                    </div>
                </div>
            @else
                {{ $slot }}
            @endif
        </flux:main>
    @endif
</section>
