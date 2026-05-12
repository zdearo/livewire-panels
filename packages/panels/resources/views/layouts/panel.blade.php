@php($currentPanel = app(\Zdearo\LivewirePanels\PanelManager::class)->getCurrentPanel())

<x-dynamic-component :component="$appLayout ?? $currentPanel?->appLayout ?? 'livewire-panels::layouts.app'">
    <div data-livewire-panels-layout="panel">
        <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700" data-livewire-panels-navigation>
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
                @isset($navigation)
                    {{ $navigation }}
                @else
                    @php($navigationItems = $currentPanel?->navigationItems() ?? [])
                    @php($renderedGroups = [])

                    @foreach($navigationItems as $navigationItem)
                        @if($navigationItem->group === null)
                            <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->badge" :href="$navigationItem->url ?? '#'" :current="$navigationItem->isCurrent()">
                                {{ $navigationItem->label }}
                            </flux:sidebar.item>
                        @elseif(! in_array($navigationItem->group, $renderedGroups, true))
                            @php($group = $navigationItem->group)
                            @php($groupItems = array_values(array_filter($navigationItems, fn ($item) => $item->group === $group)))
                            @php($renderedGroups[] = $group)

                            <flux:sidebar.group expandable heading="{{ $group }}" class="grid">
                                @foreach($groupItems as $groupItem)
                                    <flux:sidebar.item :icon="$groupItem->icon" :badge="$groupItem->badge" :href="$groupItem->url ?? '#'" :current="$groupItem->isCurrent()">
                                        {{ $groupItem->label }}
                                    </flux:sidebar.item>
                                @endforeach
                            </flux:sidebar.group>
                        @endif
                    @endforeach
                @endisset
            </flux:sidebar.nav>

            <flux:sidebar.spacer />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />
        </flux:header>

        <flux:main data-livewire-panels-content>
            {{ $slot }}
        </flux:main>
    </div>
</x-dynamic-component>
