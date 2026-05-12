<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelProvider;

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
