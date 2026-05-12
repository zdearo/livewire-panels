<?php

declare(strict_types=1);

use Livewire\Livewire;
use Zdearo\LivewirePanels\NavigationGroup;
use Zdearo\LivewirePanels\NavigationItem;
use Zdearo\LivewirePanels\Page;
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelManager;

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

    Livewire::test('livewire-panels::panel-sidebar')
        ->assertSeeHtml('<section')
        ->assertSeeHtml('data-livewire-panels-navigation')
        ->assertSee('Acme Inc.')
        ->assertSee('Dashboard')
        ->assertSee('Management')
        ->assertSee('Users')
        ->assertSee('Settings')
        ->assertDontSee('Hidden');
});
