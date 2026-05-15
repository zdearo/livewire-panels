<?php

declare(strict_types=1);

use Livewire\Livewire;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

it('registers the panel sidebar as a Livewire component', function (): void {
    expect(app('livewire')->exists('livewire-panels::panel-sidebar'))->toBeTrue();
});

it('renders the current panel navigation contract', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationGroups([
            NavigationGroup::make('management')
                ->label('Management')
                ->icon('heroicon-o-briefcase'),
        ])
        ->pages([
            Page::make('/hidden', 'pages::admin.hidden'),
            Page::make('/', 'pages::admin.dashboard')
                ->navigation('Dashboard', icon: 'heroicon-o-home', sort: 10),
            Page::make('/users', 'pages::admin.users')
                ->navigation('Users', icon: 'heroicon-o-users', group: 'management', sort: 20),
        ])
        ->navigation([
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('heroicon-o-cog-6-tooth')
                ->sort(30),
        ]);

    app(PanelManager::class)->setCurrentPanel($panel);

    Livewire::test('livewire-panels::panel-sidebar')
        ->assertSeeHtml('<section')
        ->assertSeeHtml('data-livewire-panels-navigation')
        ->assertSeeHtml('data-blade-icon="heroicon-o-home"')
        ->assertSeeHtml('data-blade-icon="heroicon-o-briefcase"')
        ->assertSee('Acme Inc.')
        ->assertSee('Dashboard')
        ->assertSee('Management')
        ->assertSee('Users')
        ->assertSee('Settings')
        ->assertDontSee('Hidden');
});
