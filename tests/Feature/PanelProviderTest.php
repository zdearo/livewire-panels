<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelProvider;
use Zdearo\LivewirePanels\Panel\PanelRegistry;

it('automatically registers its panel in the registry', function (): void {
    app()->register(TestingPanelProvider::class);

    $panel = app(PanelRegistry::class)->get('admin');

    expect($panel)
        ->toBeInstanceOf(Panel::class)
        ->id->toBe('admin')
        ->path->toBe('admin')
        ->name->toBe('Admin')
        ->appLayout->toBe('custom-app-layout')
        ->layout->toBe('custom-layout')
        ->vite->toBe([
            'resources/css/admin.css',
            'resources/js/admin.js',
        ])
        ->middleware->toBe(['web', 'auth'])
        ->withoutMiddleware->toBe(['csrf'])
        ->pages->toHaveCount(1)
        ->pages->sequence(
            fn ($page) => $page
                ->toBeInstanceOf(Page::class)
                ->path->toBe('/')
                ->component->toBe('pages::admin.dashboard')
                ->name->toBe('dashboard'),
        );
});

it('uses the default app layout when no custom app layout is configured', function (): void {
    $panel = Panel::make();

    expect($panel->appLayout)->toBe('livewire-panels::layouts.app');
});

it('uses the default panel layout when no custom layout is configured', function (): void {
    $panel = Panel::make();

    expect($panel->layout)->toBe('livewire-panels::layouts.panel');
});

it('does not assume app Vite entrypoints by default', function (): void {
    $panel = Panel::make();

    expect($panel->vite)->toBe([]);
});

it('can configure the panel subdomain', function (): void {
    $panel = Panel::make()
        ->subdomain('{company}');

    expect($panel->subdomain)->toBe('{company}');
});

it('uses sidebar navigation mode by default', function (): void {
    $panel = Panel::make();

    expect($panel->navigationMode)->toBe(NavigationMode::Sidebar);
});

it('can configure the panel navigation mode', function (): void {
    $panel = Panel::make()
        ->navigationMode(NavigationMode::TopbarWithSidebar);

    expect($panel->navigationMode)->toBe(NavigationMode::TopbarWithSidebar);
});

it('can configure the panel navigation mode from a string', function (): void {
    $panel = Panel::make()
        ->navigationMode('topbar');

    expect($panel->navigationMode)->toBe(NavigationMode::Topbar);
});

it('does not allow panel properties to be changed externally', function (): void {
    app()->register(TestingPanelProvider::class);

    $panel = app(PanelRegistry::class)->get('admin');

    expect(fn () => $panel->id = 'app')->toThrow(Error::class);
});

it('does not allow panel ids to be assigned twice', function (): void {
    $panel = Panel::make()->id('admin');

    expect(fn () => $panel->id('app'))
        ->toThrow(LogicException::class, 'The panel already has the ID [admin].');
});

it('returns the default panel when no panel id is requested', function (): void {
    app()->register(DefaultTestingPanelProvider::class);
    app()->register(SecondaryTestingPanelProvider::class);

    $registry = app(PanelRegistry::class);

    expect($registry->getDefault())
        ->toBe($registry->get())
        ->id->toBe('admin');
});

it('can find a panel using a normalized id when strict lookup is disabled', function (): void {
    app()->register(DefaultTestingPanelProvider::class);
    app()->register(SecondaryTestingPanelProvider::class);

    $panel = app(PanelRegistry::class)->get('sales_panel', isStrict: false);

    expect($panel->id)->toBe('sales-panel');
});

it('returns all registered panels keyed by id', function (): void {
    app()->register(DefaultTestingPanelProvider::class);
    app()->register(SecondaryTestingPanelProvider::class);

    $panels = app(PanelRegistry::class)->all();

    expect($panels)
        ->toHaveKeys(['admin', 'sales-panel'])
        ->and($panels['admin']->id)->toBe('admin')
        ->and($panels['sales-panel']->id)->toBe('sales-panel');
});

it('builds a navigation contract from declared groups, opted-in pages, and manual items', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationGroups([
            NavigationGroup::make('management')
                ->label('Management')
                ->icon('briefcase')
                ->sort(20),
        ])
        ->pages([
            Page::make('/hidden', 'pages::admin.hidden')->name('hidden'),
            Page::make('/', 'pages::admin.dashboard')
                ->name('dashboard')
                ->navigation('Dashboard', icon: 'home', sort: 10),
            Page::group('/settings')
                ->name('settings')
                ->pages([
                    Page::make('/', 'pages::admin.settings.index')
                        ->name('index')
                        ->navigation('Settings', icon: 'cog-6-tooth', sort: 15),
                ]),
            Page::make('/users', 'pages::admin.users')
                ->name('users')
                ->navigation('Users', icon: 'users', group: 'management', sort: 20),
        ])
        ->navigation([
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('cog-6-tooth')
                ->sort(30),
        ]);

    $navigation = $panel->navigationContract();

    expect($navigation)
        ->toBeInstanceOf(NavigationContract::class)
        ->and($navigation->items())
        ->toHaveCount(3)
        ->sequence(
            fn ($item) => $item
                ->label->toBe('Dashboard')
                ->url->toBe('/admin')
                ->icon->toBe('home')
                ->sort->toBe(10),
            fn ($item) => $item
                ->label->toBe('Settings')
                ->url->toBe('/admin/settings')
                ->icon->toBe('cog-6-tooth')
                ->sort->toBe(15),
            fn ($item) => $item
                ->label->toBe('Settings')
                ->url->toBe('/admin/settings')
                ->icon->toBe('cog-6-tooth')
                ->sort->toBe(30),
        )
        ->and($navigation->groups())
        ->toHaveCount(1)
        ->sequence(
            fn ($group) => $group
                ->id->toBe('management')
                ->label->toBe('Management')
                ->icon->toBe('briefcase')
                ->sort->toBe(20)
                ->items->toHaveCount(1)
                ->items->sequence(
                    fn ($item) => $item
                        ->label->toBe('Users')
                        ->url->toBe('/admin/users')
                        ->group->toBe('management')
                        ->sort->toBe(20),
                ),
        )
        ->and($navigation->allItems())
        ->toHaveCount(4);
});

it('fails when a navigation item references an undeclared group', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->page(
            Page::make('/users', 'pages::admin.users')
                ->navigation('Users', group: 'management'),
        );

    expect(fn () => $panel->navigationContract())
        ->toThrow(LogicException::class, 'Navigation group [management] has not been registered for panel [admin].');
});

it('can still return a flat navigation item list from the navigation contract', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationGroups(NavigationGroup::make('management'))
        ->pages([
            Page::make('/users', 'pages::admin.users')
                ->navigation('Users', group: 'management', sort: 20),
            Page::make('/', 'pages::admin.dashboard')
                ->navigation('Dashboard', sort: 10),
        ]);

    expect($panel->navigationItems())
        ->toHaveCount(2)
        ->sequence(
            fn ($item) => $item->label->toBe('Dashboard'),
            fn ($item) => $item->label->toBe('Users'),
        );
});

final class TestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->appLayout('custom-app-layout')
            ->layout('custom-layout')
            ->vite(['resources/css/admin.css', 'resources/js/admin.js'])
            ->middleware(['web', 'auth'])
            ->withoutMiddleware('csrf')
            ->pages([
                Page::make('/', 'pages::admin.dashboard')->name('dashboard'),
            ]);
    }
}

final class DefaultTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->default();
    }
}

final class SecondaryTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sales-panel')
            ->path('sales')
            ->name('Sales');
    }
}
