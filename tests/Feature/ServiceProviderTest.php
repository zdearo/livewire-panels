<?php

declare(strict_types=1);

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelManager;

beforeEach(function (): void {
    fakeViteForLivewirePanelsTests();
});

it('does not register panel providers through package configuration', function (): void {
    expect(config('livewire-panels'))->toBeNull();
});

it('loads the package app layout views', function (): void {
    expect(view()->exists('livewire-panels::layouts.app'))->toBeTrue();
});

it('loads the package panel layout views', function (): void {
    expect(view()->exists('livewire-panels::layouts.panel'))->toBeTrue();
});

it('provides a panel stylesheet source for the consuming app build', function (): void {
    $sourcePath = __DIR__.'/../../packages/panels/resources/css/panels.css';

    expect($sourcePath)
        ->toBeFile()
        ->and(File::get($sourcePath))
        ->not->toContain("@import 'tailwindcss'")
        ->toContain("@import '../../../../vendor/livewire/flux/dist/flux.css';")
        ->toContain('[data-livewire-panels-layout="panel"]');
});

it('wraps the panel layout with the package app layout', function (): void {
    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('<!DOCTYPE html>')
        ->toContain('data-livewire-panels-layout="app"')
        ->not->toContain('vite:resources/css/app.css|resources/js/app.js')
        ->not->toContain('/vendor/livewire-panels/panels.css')
        ->toContain('class="min-h-screen bg-white dark:bg-zinc-800 antialiased"')
        ->toContain('data-livewire-panels-layout="panel"')
        ->toContain('Panel body');
});

it('renders the default Flux sidebar shell around panel content', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('Acme Inc.')
        ->toContain('Home')
        ->toContain('Inbox')
        ->toContain('Documents')
        ->toContain('Calendar')
        ->toContain('Favorites')
        ->toContain('Settings')
        ->toContain('Help')
        ->toContain('Olivia Martin')
        ->toContain('Panel body');
});

it('does not render fixed app Vite entrypoints when no panel entrypoints are configured', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->not->toContain('vite:resources/css/app.css|resources/js/app.js')
        ->not->toContain('vite:')
        ->not->toContain('resources/css/panels/admin.css')
        ->toContain('Panel body');
});

it('renders configured panel Vite entrypoints', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->vite(['resources/css/panel.css', 'resources/js/panel.js']);

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('vite:resources/css/panel.css|resources/js/panel.js')
        ->toContain('Panel body');
});

it('can render the panel layout while a current panel is set', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('data-livewire-panels-layout="app"')
        ->toContain('Panel body');
});

function fakeViteForLivewirePanelsTests(): void
{
    app()->instance(Vite::class, new class
    {
        /**
         * @param  array<int, string>|string  $entrypoints
         */
        public function __invoke(array|string $entrypoints, ?string $buildDirectory = null): HtmlString
        {
            return new HtmlString('vite:'.implode('|', (array) $entrypoints));
        }
    });
}
