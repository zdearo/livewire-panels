<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelProvider;

it('automatically registers panel routes inside the panel route group', function (): void {
    app()->register(RouteTestingPanelProvider::class);
    app()->boot();

    expect(app()->isBooted())->toBeTrue();

    $route = Route::getRoutes()->getByName('admin.dashboard');

    expect($route)
        ->not->toBeNull()
        ->uri()->toBe('admin')
        ->gatherMiddleware()->toContain('web', 'auth')
        ->excludedMiddleware()->toBe(['csrf']);
});

it('automatically registers panel pages as Livewire page routes', function (): void {
    app()->register(PageRouteTestingPanelProvider::class);
    app()->boot();

    $route = Route::getRoutes()->getByName('admin.dashboard');

    expect($route)
        ->not->toBeNull()
        ->uri()->toBe('admin')
        ->gatherMiddleware()->toContain('web', SetCurrentPanel::class.':admin')
        ->and($route?->getAction('livewire_component'))->toBe('pages::admin.dashboard');
});

it('automatically registers grouped panel pages with path and name prefixes', function (): void {
    app()->register(GroupedPageRouteTestingPanelProvider::class);
    app()->boot();

    $indexRoute = Route::getRoutes()->getByName('admin.settings.index');
    $usersRoute = Route::getRoutes()->getByName('admin.settings.users');

    expect($indexRoute)
        ->not->toBeNull()
        ->uri()->toBe('admin/settings')
        ->and($indexRoute?->getAction('livewire_component'))->toBe('pages::admin.settings.index')
        ->and($usersRoute)
        ->not->toBeNull()
        ->uri()->toBe('admin/settings/users')
        ->and($usersRoute?->getAction('livewire_component'))->toBe('pages::admin.settings.users');
});

final class RouteTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->middleware(['web', 'auth'])
            ->withoutMiddleware('csrf')
            ->routes(function (): void {
                Route::get('/', fn (): string => 'Dashboard')->name('dashboard');
            });
    }
}

final class PageRouteTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->middleware(['web'])
            ->page(Page::make('/', 'pages::admin.dashboard')->name('dashboard'));
    }
}

final class GroupedPageRouteTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->middleware(['web'])
            ->pages([
                Page::group('/settings')
                    ->name('settings')
                    ->pages([
                        Page::make('/', 'pages::admin.settings.index')->name('index'),
                        Page::make('/users', 'pages::admin.settings.users')->name('users'),
                    ]),
            ]);
    }
}
