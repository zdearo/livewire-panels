## Livewire Panels

Livewire Panels provides developer-defined multi-panel infrastructure for Laravel applications using Livewire 4 and Flux UI.

### Core Principles

- Panels are defined by developers in PHP using panel providers.
- Do not list panels in package config files.
- Do not force a fixed app structure for user-created pages or panels.
- The package owns reusable infrastructure: panel objects, providers, registries, routing, navigation, authentication middleware, shell hooks, and generator commands.
- The consuming app or starter kit owns login pages, dashboards, application models, and app-specific Livewire components.

### Panel Providers

Create panels through `PanelProvider` classes registered by Laravel's provider mechanism.

@verbatim
<code-snippet name="Panel Provider Example" lang="php">
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationMode;
use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelProvider;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->vite('resources/css/panels/admin.css')
            ->navigationMode(NavigationMode::TopbarWithSidebar)
            ->navigationGroups([
                NavigationGroup::make('management')->label('Management')->icon('heroicon-o-users'),
            ])
            ->pages([
                Page::make('/', 'pages::admin.dashboard')
                    ->name('dashboard')
                    ->navigation('Dashboard', icon: 'heroicon-o-home'),

                Page::make('/users', 'pages::admin.users')
                    ->name('users')
                    ->navigation('Users', icon: 'heroicon-o-users', group: 'management'),
            ])
            ->default();
    }
}
</code-snippet>
@endverbatim

### Pages

Use `Page::make()` as a route descriptor. The Livewire component remains native Livewire 4 and may be SFC, MFC, or class-based.

@verbatim
<code-snippet name="Panel Page Descriptor" lang="php">
Page::make('/users', 'pages::admin.users')
    ->name('users')
    ->navigation('Users', icon: 'heroicon-o-users');
</code-snippet>
@endverbatim

Use `Page::group()` only for route path and route name grouping. Navigation groups are separate and must be declared through `navigationGroups()`.

### Navigation

Pages do not appear in navigation unless `navigation()` is called. Navigation group references use group IDs and must reference explicitly declared groups.

Available navigation modes are:

@verbatim
<code-snippet name="Navigation Modes" lang="php">
$panel->navigationMode('sidebar');
$panel->navigationMode('topbar');
$panel->navigationMode('topbar-sidebar');

$panel->navigationMode(fn (): NavigationMode => auth()->user()?->prefers_topbar
    ? NavigationMode::Topbar
    : NavigationMode::Sidebar);
</code-snippet>
@endverbatim

Lazy navigation modes are allowed because they affect rendering only. Do not make route structure, middleware, guards, tenant route parameters, or Vite entrypoints lazy.

When state used by a lazy navigation mode changes during an active Livewire session, dispatch `livewire-panels::refresh-navigation` after persisting the state. Do not send the target mode as event payload; the package must resolve the panel closure again.

Do not implement hover-driven Livewire state for navigation. The `topbar` mode may use Flux hover dropdowns locally, but navigation should only happen when a page/item link is clicked.

Navigation active state should resolve from the package's original request handling, not from the Livewire update endpoint.

Navigation and shell icon names use Blade Icons names, such as `heroicon-o-users`. Package-owned fixed shell icons can be replaced through aliases.

@verbatim
<code-snippet name="Panel Shell Icon Aliases" lang="php">
use Zdearo\LivewirePanels\Facades\PanelsIcon;
use Zdearo\LivewirePanels\Icons\PanelsIconAlias;

PanelsIcon::register([
    PanelsIconAlias::SIDEBAR_TOGGLE_BUTTON => 'heroicon-o-queue-list',
    PanelsIconAlias::TOPBAR_GROUP_DROPDOWN_BUTTON => view('icons.chevron-down'),
    PanelsIconAlias::USER_MENU_LOGOUT_BUTTON => 'heroicon-o-arrow-left-start-on-rectangle',
]);
</code-snippet>
@endverbatim

### Panel Shells

Customize the Flux shell through a shell class, not by adding many visual flags to the provider.

@verbatim
<code-snippet name="Custom Panel Shell" lang="php">
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;

final class AdminPanelShell extends DefaultPanelShell
{
    public function sidebarBrand(Panel $panel): View|Htmlable|string|null
    {
        return view('panels.admin.sidebar-brand', ['panel' => $panel]);
    }

    public function topbarEnd(Panel $panel): View|Htmlable|string|null
    {
        return view('panels.admin.topbar-actions', ['panel' => $panel]);
    }
}
</code-snippet>
@endverbatim

Available shell hooks include `sidebarBrand()`, `topbarBrand()`, `mobileSidebarBrand()`, `sidebarFooter()`, `topbarEnd()`, and `mobileHeaderEnd()`.

Prefer extending `DefaultPanelShell` when the app wants package defaults plus local customization. Extend `PanelShell` only when replacing all defaults intentionally.

### Authentication

Panel authentication is opt-in. Calling `authenticatables()` adds package authentication middleware and validates the authenticated model class.

@verbatim
<code-snippet name="Panel Authentication" lang="php">
$panel
    ->authGuard('admin')
    ->loginRoute('admin.login')
    ->logoutRoute('admin.logout')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
</code-snippet>
@endverbatim

If `loginRoute()` is omitted, the fallback route is `{panelId}.login`, such as `admin.login`.

The package does not provide login pages. Generate or implement login UI in the starter kit or consuming app. The default Flux user menu renders a logout action only when `logoutRoute()` is configured.

Allowed models may implement `Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel` for panel-specific access rules.

### Generator

Use the package generator for panel providers and panel CSS entrypoints:

@verbatim
<code-snippet name="Make Panel" lang="bash">
php artisan make:panel admin
php artisan make:panel admin --shell
</code-snippet>
@endverbatim

The `--shell` option creates `app/Panels/Admin/AdminPanelShell.php` and wires it with `->shell(AdminPanelShell::class)`.

Do not create a page generator in this package. Developers should create Livewire components with Livewire tooling and register them with `Page::make(...)`.

### Facade

Use the facade when app code needs panel state:

@verbatim
<code-snippet name="Current Panel Facade" lang="php">
use Zdearo\LivewirePanels\Facades\LivewirePanels;

$panel = LivewirePanels::currentPanel();
</code-snippet>
@endverbatim

### Testing

When changing the package, run:

@verbatim
<code-snippet name="Validate Livewire Panels" lang="bash">
composer test
</code-snippet>
@endverbatim

This runs Rector dry-run, Pint, PHPStan, and Pest with coverage.
