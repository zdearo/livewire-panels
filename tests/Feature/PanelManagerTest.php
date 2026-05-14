<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Panel\PanelProvider;

it('is registered as a singleton', function (): void {
    expect(app(PanelManager::class))->toBe(app(PanelManager::class));
});

it('can resolve panels through the registry', function (): void {
    app()->register(ManagerDefaultPanelProvider::class);
    app()->register(ManagerSecondaryPanelProvider::class);

    $panel = app(PanelManager::class)->panel('sales_panel', isStrict: false);

    expect($panel->id)->toBe('sales-panel');
});

it('stores the current panel', function (): void {
    app()->register(ManagerDefaultPanelProvider::class);

    $manager = app(PanelManager::class);
    $panel = $manager->panel('admin');

    $manager->setCurrentPanel($panel);

    expect($manager->currentPanel())->toBe($panel);
});

it('exposes the panel manager through a facade', function (): void {
    app()->register(ManagerDefaultPanelProvider::class);

    $panel = Panels::panel('admin');

    Panels::setCurrentPanel($panel);

    expect(Panels::currentPanel())->toBe($panel)
        ->and(app(PanelManager::class)->currentPanel)->toBe($panel);
});

it('exposes default and registered panels through a facade', function (): void {
    app()->register(ManagerDefaultPanelProvider::class);
    app()->register(ManagerSecondaryPanelProvider::class);

    expect(Panels::defaultPanel())
        ->toBeInstanceOf(Panel::class)
        ->id->toBe('admin')
        ->and(Panels::panels())
        ->toHaveKeys(['admin', 'sales-panel']);
});

it('creates panel instances without resolving them from the container', function (): void {
    $boundPanel = new Panel;

    app()->instance(Panel::class, $boundPanel);

    expect(Panel::make())->not->toBe($boundPanel);
});

it('uses the default registered panel as the current panel', function (): void {
    app()->register(ManagerSecondaryPanelProvider::class);
    app()->register(ManagerDefaultPanelProvider::class);

    expect(app(PanelManager::class)->currentPanel())
        ->toBeInstanceOf(Panel::class)
        ->id->toBe('admin');
});

final class ManagerDefaultPanelProvider extends PanelProvider
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

final class ManagerSecondaryPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sales-panel')
            ->path('sales')
            ->name('Sales');
    }
}
