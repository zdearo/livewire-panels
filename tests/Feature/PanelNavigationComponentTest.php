<?php

declare(strict_types=1);

use Livewire\Livewire;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Navigation\NavigationMode;
use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

it('registers the panel navigation as a Livewire component', function (): void {
    expect(app('livewire')->exists('livewire-panels::panel-navigation'))->toBeTrue();
});

it('renders the sidebar navigation mode by default', function (): void {
    app(PanelManager::class)->setCurrentPanel(navigationTestingPanel());

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="sidebar"')
        ->assertSeeHtml('data-livewire-panels-primary-sidebar')
        ->assertSee('Dashboard')
        ->assertSee('Management')
        ->assertSee('Users')
        ->assertSee('Settings')
        ->assertDontSee('Hidden');
});

it('renders grouped navigation in the topbar mode', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::Topbar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertSeeHtml('data-livewire-panels-topbar')
        ->assertSee('Dashboard')
        ->assertSee('Management')
        ->assertSee('Users')
        ->assertSee('Settings')
        ->assertDontSeeHtml('data-livewire-panels-secondary-navigation');
});

it('renders active group items in the secondary sidebar for topbar with sidebar mode', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSet('activeGroupId', 'management')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar-sidebar"')
        ->assertSeeHtml('data-livewire-panels-active-group="management"')
        ->assertSee('Users')
        ->call('setActiveGroup', 'content')
        ->assertSet('activeGroupId', 'content')
        ->assertSeeHtml('data-livewire-panels-active-group="content"')
        ->assertSee('Posts');
});

function navigationTestingPanel(): Panel
{
    return Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->navigationGroups([
            NavigationGroup::make('management')
                ->label('Management')
                ->icon('briefcase')
                ->sort(10),
            NavigationGroup::make('content')
                ->label('Content')
                ->icon('document-text')
                ->sort(20),
        ])
        ->pages([
            Page::make('/hidden', 'pages::admin.hidden'),
            Page::make('/', 'pages::admin.dashboard')
                ->navigation('Dashboard', icon: 'home', sort: 10),
            Page::make('/users', 'pages::admin.users')
                ->navigation('Users', icon: 'users', group: 'management', sort: 20),
            Page::make('/posts', 'pages::admin.posts')
                ->navigation('Posts', icon: 'document-text', group: 'content', sort: 30),
        ])
        ->navigation([
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('cog-6-tooth')
                ->sort(40),
        ]);
}
