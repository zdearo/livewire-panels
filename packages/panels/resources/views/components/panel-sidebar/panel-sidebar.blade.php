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
                <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->badge" :href="$navigationItem->url ?? '#'" :current="$navigationItem->isCurrent()">
                    {{ $navigationItem->label }}
                </flux:sidebar.item>
            @endforeach

            @foreach($this->navigationContract()?->groups() ?? [] as $navigationGroup)
                <flux:sidebar.group expandable :icon="$navigationGroup->icon" heading="{{ $navigationGroup->label }}" class="grid">
                    @foreach($navigationGroup->items as $navigationItem)
                        <flux:sidebar.item :icon="$navigationItem->icon" :badge="$navigationItem->badge" :href="$navigationItem->url ?? '#'" :current="$navigationItem->isCurrent()">
                            {{ $navigationItem->label }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @endforeach
        </flux:sidebar.nav>

        <flux:sidebar.spacer />
    </flux:sidebar>
</section>
