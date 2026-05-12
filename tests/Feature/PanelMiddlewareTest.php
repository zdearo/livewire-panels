<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Panel\PanelRegistry;

it('sets the current panel and Livewire page layout for panel requests', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->layout('custom-panel-layout');

    app(PanelRegistry::class)->register($panel);

    $response = app(SetCurrentPanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect($response->getContent())->toBe('ok')
        ->and(app(PanelManager::class)->getCurrentPanel())->toBe($panel)
        ->and(config('livewire.component_layout'))->toBe('custom-panel-layout');
});
