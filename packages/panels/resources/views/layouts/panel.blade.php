@php($currentPanel = app(\Zdearo\LivewirePanels\Panel\PanelManager::class)->getCurrentPanel())

<x-dynamic-component :component="$appLayout ?? $currentPanel?->appLayout ?? 'livewire-panels::layouts.app'">
    <div data-livewire-panels-layout="panel">
        <livewire:livewire-panels::panel-sidebar />

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />
        </flux:header>

        <flux:main data-livewire-panels-content>
            {{ $slot }}
        </flux:main>
    </div>
</x-dynamic-component>
