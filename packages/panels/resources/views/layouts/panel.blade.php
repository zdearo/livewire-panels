@php
$currentPanel = \Zdearo\LivewirePanels\Facades\Panels::currentPanel();
@endphp

<x-dynamic-component :component="$appLayout ?? $currentPanel?->appLayout ?? 'livewire-panels::layouts.app'">
   <div data-livewire-panels-layout="panel">
      <livewire:livewire-panels::panel-navigation />

      <flux:main data-livewire-panels-content>
         <div class="flex max-md:flex-col items-start">
            <livewire:livewire-panels::panel-secondary-navigation />

            <div class="flex-1 self-stretch" data-livewire-panels-page-content>
               {{ $slot }}
            </div>
         </div>
      </flux:main>
   </div>
</x-dynamic-component>