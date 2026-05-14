<?php

declare(strict_types=1);

use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

use function Livewire\invade;

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

it('binds the current request as the original request outside Livewire requests', function (): void {
    app()->instance('request', Request::create('/admin/users'));

    expect(app('originalRequest'))
        ->toBeInstanceOf(Request::class)
        ->path()->toBe('admin/users');
});

it('binds a fake original route request during Livewire requests', function (): void {
    Route::get('/admin/users', fn (): string => 'Users')->name('admin.users');

    app()->instance('request', Request::create('/livewire/update', 'POST', [], [], [], [
        'HTTP_X_LIVEWIRE' => 'true',
    ]));

    $persistentMiddleware = app(PersistentMiddleware::class);
    invade($persistentMiddleware)->path = 'admin/users';
    invade($persistentMiddleware)->method = 'GET';

    $request = app('originalRequest');

    expect($request)
        ->toBeInstanceOf(Request::class)
        ->path()->toBe('admin/users')
        ->and($request->route()?->getName())->toBe('admin.users');
});

it('provides a panel stylesheet source for the consuming app build', function (): void {
    $sourcePath = __DIR__.'/../../packages/panels/resources/css/panels.css';

    expect($sourcePath)
        ->toBeFile()
        ->and(File::get($sourcePath))
        ->not->toContain("@import 'tailwindcss'")
        ->not->toContain('vendor/livewire/flux')
        ->toContain('[data-livewire-panels-layout="panel"]')
        ->toContain('[data-livewire-panels-navigation]')
        ->toContain('[data-livewire-panels-secondary-navigation-shell]')
        ->toContain('display: contents;')
        ->toContain('[data-livewire-panels-layout="panel"]:has([data-livewire-panels-primary-sidebar]) [data-livewire-panels-content]')
        ->toContain('[data-livewire-panels-primary-sidebar][data-flux-sidebar-collapsed-desktop]')
        ->toContain('cursor: default;');
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

it('keeps the panel body outside the Livewire navigation component', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('wire:snapshot')
        ->and(strpos($html, 'Panel body'))->toBeGreaterThan(strpos($html, '</section>'));
});

it('renders the configured panel navigation mode in the panel layout', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationMode('topbar');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('data-livewire-panels-navigation-mode="topbar"')
        ->toContain('Panel body');
});

it('renders the default Flux sidebar shell without demo navigation items', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('wire:snapshot')
        ->toContain('Admin')
        ->not->toContain('Acme Inc.')
        ->not->toContain('Inbox')
        ->not->toContain('Documents')
        ->not->toContain('Favorites')
        ->not->toContain('Settings')
        ->not->toContain('Help')
        ->not->toContain('Olivia Martin')
        ->toContain('Panel body');
});

it('renders configured panel navigation items in the Flux sidebar', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationGroups([
            NavigationGroup::make('management')
                ->label('Management')
                ->icon('briefcase'),
        ])
        ->pages([
            Page::make('/hidden', 'pages::admin.hidden'),
            Page::make('/', 'pages::admin.dashboard')
                ->navigation('Dashboard', icon: 'home', sort: 10),
            Page::make('/users', 'pages::admin.users')
                ->navigation('Users', icon: 'users', group: 'management', sort: 20),
        ])
        ->navigation([
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('cog-6-tooth')
                ->sort(30),
        ]);

    app(PanelManager::class)->setCurrentPanel($panel);

    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('Dashboard')
        ->toContain('Management')
        ->toContain('Users')
        ->toContain('Settings')
        ->not->toContain('Hidden')
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
