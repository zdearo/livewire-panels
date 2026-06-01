@php
    $mode = $this->navigationMode();
    $activeGroup = $this->activeGroup();
@endphp

<section
    data-livewire-panels-secondary-navigation-shell
    data-livewire-panels-navigation-mode="{{ $mode->value }}"
>
    @if($mode === \Zdearo\LivewirePanels\Enums\NavigationMode::TopbarWithSidebar && $activeGroup !== null)
        <div class="w-full md:w-[220px] pb-4 me-10" data-livewire-panels-secondary-navigation data-livewire-panels-active-group="{{ $activeGroup->id }}">
            <flux:navlist>
                @foreach($activeGroup->items as $navigationItem)
                    <flux:navlist.item
                        :wire:navigate="$this->navigationItemUsesSpa($navigationItem)"
                        :href="$navigationItem->displayUrl() ?? '#'"
                        :badge="$navigationItem->displayBadge()"
                        :current="$this->navigationItemIsCurrent($navigationItem)"
                    >
                        {{ $navigationItem->displayLabel() }}
                    </flux:navlist.item>
                @endforeach
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />
    @endif
</section>
