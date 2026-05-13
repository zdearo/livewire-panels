<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Navigation\NavigationMode;
use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Shell\PanelShell;

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

it('renders a custom panel shell', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->shell(CustomPanelShell::class),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-custom-sidebar-brand')
        ->assertSee('Custom Admin')
        ->assertSeeHtml('data-custom-sidebar-footer');
});

it('renders the default authenticated user menu in the sidebar shell', function (): void {
    $this->be((new PanelNavigationUser)->forceFill(['name' => 'Olivia Martin']));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->authenticatables(PanelNavigationUser::class),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-user-menu')
        ->assertSee('Olivia Martin');
});

it('renders the default authenticated user menu in the topbar shell', function (): void {
    $this->be((new PanelNavigationUser)->forceFill(['name' => 'Olivia Martin']));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->authenticatables(PanelNavigationUser::class)
            ->navigationMode(NavigationMode::Topbar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-user-menu')
        ->assertSee('Olivia Martin');
});

it('renders grouped navigation in the topbar mode', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::Topbar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertSeeHtml('data-livewire-panels-topbar')
        ->assertSeeHtml('data-livewire-panels-navigation-dropdown')
        ->assertSee('Dashboard')
        ->assertSee('Management')
        ->assertSee('Users')
        ->assertSee('Settings')
        ->assertDontSeeHtml('wire:mouseenter')
        ->assertDontSeeHtml('data-livewire-panels-secondary-navigation');
});

it('renders current page group items in the secondary sidebar for topbar with sidebar mode', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar-sidebar"')
        ->assertDontSeeHtml('wire:click')
        ->assertDontSeeHtml('wire:mouseenter');
});

it('resolves a group URL from the first item in that group', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    $component = panelNavigationComponent();

    expect($component->groupUrl($component->navigationGroups()[0]))->toBe('/admin/users')
        ->and($component->groupUrl($component->navigationGroups()[1]))->toBe('/admin/posts');
});

it('resolves the active group from the current page', function (): void {
    requestPath('/admin/users');

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    $component = panelNavigationComponent();

    expect($component->activeGroup())
        ->not->toBeNull()
        ->id->toBe('management');
});

it('resolves the active group from the original request during Livewire updates', function (): void {
    app()->instance('request', Request::create('/livewire/update'));
    app()->instance('originalRequest', Request::create('/admin/users'));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    $activeGroup = panelNavigationComponent()->activeGroup();

    expect($activeGroup)
        ->not->toBeNull()
        ->id->toBe('management')
        ->and($activeGroup->items[0]->isCurrent())->toBeTrue();
});

it('does not resolve an active group when the current page has no group', function (): void {
    requestPath('/admin');

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    expect(panelNavigationComponent()->activeGroup())->toBeNull();
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

function requestPath(string $path): void
{
    app()->instance('request', Request::create($path));
}

function panelNavigationComponent(): object
{
    return require __DIR__.'/../../packages/panels/resources/views/components/panel-navigation/panel-navigation.php';
}

final class CustomPanelShell extends PanelShell
{
    #[Override]
    public function sidebarBrand(Panel $panel): string
    {
        return '<div data-custom-sidebar-brand>Custom '.$panel->name.'</div>';
    }

    #[Override]
    public function sidebarFooter(Panel $panel): string
    {
        return '<div data-custom-sidebar-footer>Custom footer</div>';
    }
}

final class PanelNavigationUser extends Authenticatable {}
