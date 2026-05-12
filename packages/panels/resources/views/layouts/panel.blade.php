<div data-livewire-panels-layout="panel">
    <aside data-livewire-panels-navigation>
        {{ $navigation ?? '' }}
    </aside>

    <main data-livewire-panels-content>
        {{ $slot }}
    </main>
</div>
