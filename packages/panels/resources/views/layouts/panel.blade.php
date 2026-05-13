@php($currentPanel = app(\Zdearo\LivewirePanels\Panel\PanelManager::class)->getCurrentPanel())

<x-dynamic-component :component="$appLayout ?? $currentPanel?->appLayout ?? 'livewire-panels::layouts.app'">
    <div data-livewire-panels-layout="panel">
        <livewire:livewire-panels::panel-navigation>
            {{ $slot }}
        </livewire:livewire-panels::panel-navigation>
    </div>
</x-dynamic-component>
