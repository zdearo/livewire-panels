<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Shell\PanelShell;

it('registers the panel navigation as a Livewire component', function (): void {
    expect(app('livewire')->exists('livewire-panels::panel-navigation'))->toBeTrue();
});

it('registers the panel secondary navigation as a Livewire component', function (): void {
    expect(app('livewire')->exists('livewire-panels::panel-secondary-navigation'))->toBeTrue();
});

it('renders the sidebar navigation mode by default', function (): void {
    app(PanelManager::class)->setCurrentPanel(navigationTestingPanel());

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="sidebar"')
        ->assertSeeHtml('data-livewire-panels-primary-sidebar')
        ->assertSeeHtml('x-on:click.capture')
        ->assertSeeHtml('data-flux-sidebar-collapsed-desktop')
        ->assertSeeHtml('stopImmediatePropagation')
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

it('renders configured sidebar shell slots lazily', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->name(fn (): string => __('Admin'))
            ->sidebarBrand(fn (Panel $panel): string => '<div data-configured-sidebar-brand>'.$panel->displayName().'</div>')
            ->sidebarFooter(fn (): string => '<div data-configured-sidebar-footer>Sidebar footer</div>')
            ->mobileHeaderEnd(fn (): string => '<div data-configured-mobile-header-end>Mobile header</div>'),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-configured-sidebar-brand')
        ->assertSee('Admin')
        ->assertSeeHtml('data-configured-sidebar-footer')
        ->assertSeeHtml('data-configured-mobile-header-end');
});

it('renders configured topbar shell slots lazily', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->navigationMode(NavigationMode::Topbar)
            ->topbarBrand(fn (): string => '<div data-configured-topbar-brand>Topbar brand</div>')
            ->topbarEnd(fn (): string => '<div data-configured-topbar-end>Topbar end</div>')
            ->mobileSidebarBrand(fn (): string => '<div data-configured-mobile-sidebar-brand>Mobile brand</div>'),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-configured-topbar-brand')
        ->assertSeeHtml('data-configured-topbar-end')
        ->assertSeeHtml('data-configured-mobile-sidebar-brand');
});

it('renders the default authenticated user menu in the sidebar shell', function (): void {
    $this->be((new PanelNavigationUser)->forceFill([
        'email' => 'olivia@example.com',
        'name' => 'Olivia Martin',
    ]));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->authenticatables(PanelNavigationUser::class),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-user-menu')
        ->assertSeeHtml('data-livewire-panels-user-menu-identity')
        ->assertSee('Olivia Martin')
        ->assertSee('olivia@example.com')
        ->assertDontSee('Truly Delta')
        ->assertDontSee('Logout');
});

it('renders the configured logout action in the authenticated user menu', function (): void {
    Route::post('/admin/logout', fn (): string => 'Logout')->name('admin.logout');

    $this->be((new PanelNavigationUser)->forceFill([
        'email' => 'olivia@example.com',
        'name' => 'Olivia Martin',
    ]));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->authenticatables(PanelNavigationUser::class)
            ->logoutRoute('admin.logout'),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSee('Olivia Martin')
        ->assertSee('olivia@example.com')
        ->assertSee('Logout')
        ->assertSeeHtml('method="POST"')
        ->assertSeeHtml('action="http://localhost/admin/logout"')
        ->assertSeeHtml('name="_token"');
});

it('renders the default authenticated user menu in the topbar shell', function (): void {
    $this->be((new PanelNavigationUser)->forceFill([
        'email' => 'olivia@example.com',
        'name' => 'Olivia Martin',
    ]));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()
            ->authenticatables(PanelNavigationUser::class)
            ->navigationMode(NavigationMode::Topbar),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-user-menu')
        ->assertSee('Olivia Martin')
        ->assertSee('olivia@example.com');
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
        ->assertDontSeeHtml('livewire-panels::navigation-mode-updated')
        ->assertDontSeeHtml('data-livewire-panels-secondary-navigation-shell')
        ->assertDontSeeHtml('data-livewire-panels-secondary-navigation');
});

it('renders a lazy navigation mode', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(fn (): string => 'topbar'),
    );

    Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertSeeHtml('data-livewire-panels-topbar');
});

it('refreshes lazy navigation mode at runtime through the public refresh event', function (): void {
    $mode = 'sidebar';

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(function () use (&$mode): string {
            return $mode;
        }),
    );

    $component = Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="sidebar"')
        ->assertSeeHtml('livewire-panels::refresh-navigation')
        ->assertDontSeeHtml('livewire-panels::navigation-mode-updated');

    $mode = 'topbar';

    $component
        ->dispatch('livewire-panels::refresh-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertSeeHtml('data-livewire-panels-topbar');
});

it('refreshes the primary navigation active group at runtime', function (): void {
    $mode = 'topbar';

    requestPath('/admin/users');
    app()->instance('originalRequest', Request::create('/admin/users'));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(function () use (&$mode): string {
            return $mode;
        }),
    );

    $component = Livewire::test('livewire-panels::panel-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertSeeHtml('data-livewire-panels-active-group="management"')
        ->assertSee('Management')
        ->assertSee('Users');

    $mode = 'topbar-sidebar';

    $component
        ->dispatch('livewire-panels::refresh-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar-sidebar"')
        ->assertSeeHtml('data-livewire-panels-active-group="management"')
        ->assertSee('Management');
});

it('renders current page group items in the secondary sidebar for topbar with sidebar mode', function (): void {
    requestPath('/admin/users');
    app()->instance('originalRequest', Request::create('/admin/users'));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(NavigationMode::TopbarWithSidebar),
    );

    Livewire::test('livewire-panels::panel-secondary-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar-sidebar"')
        ->assertSeeHtml('data-livewire-panels-secondary-navigation')
        ->assertSeeHtml('data-livewire-panels-active-group="management"')
        ->assertSee('Users')
        ->assertDontSeeHtml('wire:click')
        ->assertDontSeeHtml('wire:mouseenter');
});

it('refreshes secondary navigation at runtime through the public refresh event', function (): void {
    $mode = 'topbar';

    requestPath('/admin/users');
    app()->instance('originalRequest', Request::create('/admin/users'));

    app(PanelManager::class)->setCurrentPanel(
        navigationTestingPanel()->navigationMode(function () use (&$mode): string {
            return $mode;
        }),
    );

    $component = Livewire::test('livewire-panels::panel-secondary-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar"')
        ->assertDontSeeHtml('data-livewire-panels-active-group')
        ->assertSeeHtml('livewire-panels::refresh-navigation');

    $mode = 'topbar-sidebar';

    $component
        ->dispatch('livewire-panels::refresh-navigation')
        ->assertSeeHtml('data-livewire-panels-navigation-mode="topbar-sidebar"')
        ->assertSeeHtml('data-livewire-panels-secondary-navigation')
        ->assertSeeHtml('data-livewire-panels-active-group="management"')
        ->assertSee('Users');
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
