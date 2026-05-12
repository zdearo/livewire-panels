<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Page;
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelProvider;
use Zdearo\LivewirePanels\PanelRegistry;

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
