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
                    <flux:sidebar.item icon="home" href="#" current>Home</flux:sidebar.item>
                    <flux:sidebar.item icon="inbox" badge="12" href="#">Inbox</flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item>

                    <flux:sidebar.group expandable icon="star" heading="Favorites" class="grid">
                        <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
                        <flux:sidebar.item href="#">Android app</flux:sidebar.item>
                        <flux:sidebar.item href="#">Brand guidelines</flux:sidebar.item>
                    </flux:sidebar.group>
                @endisset
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
                <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile avatar="https://fluxui.dev/img/demo/user.png" name="Olivia Martin" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                        <flux:menu.radio>Truly Delta</flux:menu.radio>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="start">
                <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                        <flux:menu.radio>Truly Delta</flux:menu.radio>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main data-livewire-panels-content>
            {{ $slot }}
        </flux:main>
    </div>
</x-dynamic-component>
