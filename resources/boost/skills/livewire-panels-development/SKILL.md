---
name: livewire-panels-development
description: Use when working with Livewire Panels panel providers, pages, navigation, shells, authentication, tenancy, URLs, icons, assets, generator, or package tests.
---

# Livewire Panels Development

## When to use this skill

Use this skill when a task involves `zdearo/livewire-panels`, the `Zdearo\LivewirePanels` namespace, panel providers, panel pages, navigation, shell customization, panel auth, tenancy, panel route URLs, panel assets, shell icons, `php artisan make:panel`, or package tests.

Do not use this skill for ordinary Livewire components unless they are being registered with a panel or rendered inside the panel shell.

## Boundaries

- Panels are developer-defined PHP configuration, not database records.
- Register one `PanelProvider` per panel through Laravel's provider mechanism. `PanelProvider::register()` is final and registers the panel automatically.
- Do not require panels to be listed in a package config file.
- Do not force a fixed app structure for pages or panels.
- This package owns reusable infrastructure: panels, providers, registries, routing, navigation, authentication middleware, tenancy, shell hooks, icon aliases, and the panel generator.
- The consuming app or starter kit owns login pages, dashboards, app models, and app-specific Livewire components.

## Panel providers

Create panels by extending `Zdearo\LivewirePanels\Panel\PanelProvider` and returning a configured `Panel` from `panel()`:

```php
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Page\Page;
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
            ->appLayout('livewire-panels::layouts.app')
            ->layout('livewire-panels::layouts.panel')
            ->vite('resources/css/panels/admin.css')
            ->middleware(['web'])
            ->navigationMode(NavigationMode::TopbarWithSidebar)
            ->navigationGroups([
                NavigationGroup::make('management')
                    ->label('Management')
                    ->icon('heroicon-o-users'),
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
```

Read panel state through public properties such as `$panel->id`; configure it with fluent methods. Do not add paired getters unless there is a specific reason.

## Pages and routes

Use `Zdearo\LivewirePanels\Page\Page` only as a route descriptor. The Livewire component remains native Livewire 4 and may be SFC, MFC, or class-based.

```php
Page::make('/users', 'pages::admin.users')
    ->name('users')
    ->navigation('Users', icon: 'heroicon-o-users');
```

Use `Page::group()` for route path and route name grouping only. Navigation groups are separate and must be declared with `navigationGroups()` before an item references them.

```php
Page::group('/settings')
    ->name('settings')
    ->pages([
        Page::make('/', 'pages::admin.settings.index')->name('index'),
        Page::make('/users', 'pages::admin.settings.users')->name('users'),
    ]);
```

For an `admin` panel, that registers `/admin/settings` as `admin.settings.index` and `/admin/settings/users` as `admin.settings.users`.

## Navigation

Pages do not appear in navigation unless `navigation()` is called. Group references use group IDs, not visible labels, and undeclared group IDs should fail when building the navigation contract.

Available navigation modes:

```php
$panel->navigationMode('sidebar');
$panel->navigationMode('topbar');
$panel->navigationMode('topbar-sidebar');

$panel->navigationMode(fn (): NavigationMode => auth()->user()?->prefers_topbar
    ? NavigationMode::Topbar
    : NavigationMode::Sidebar);
```

Lazy navigation modes are allowed because they affect rendering only. Do not make route paths, subdomains, middleware, layouts, Vite entries, auth guards, login routes, tenant route parameters, or sort values lazy.

When state used by a lazy navigation mode changes during an active Livewire session, dispatch `livewire-panels::refresh-navigation` after persisting the state. Do not send the target mode as event payload; the package resolves `Panel::displayNavigationMode()` again.

Use `Page::navigationUrl()` when a page's navigation item should click somewhere else while remaining active for the page and descendants:

```php
Page::make('/leads', 'pages::app.leads.index')
    ->name('leads.index')
    ->navigation('Leads')
    ->navigationUrl(fn (): string => Panels::route('leads.overview'));
```

Manual items use `NavigationItem`:

```php
use Zdearo\LivewirePanels\Navigation\NavigationItem;

$panel->navigation([
    NavigationItem::make('Settings')
        ->url('/admin/settings')
        ->icon('heroicon-o-cog-6-tooth')
        ->sort(100),
]);
```

Do not implement hover-driven Livewire state for navigation. Topbar dropdown hover is handled locally by Flux; navigation happens only when clicking links.

## Shells and icons

Customize the Flux shell through a shell class or focused slot overrides, not provider-level visual flags. A configured slot takes precedence over the shell class for that slot.

```php
use Illuminate\Contracts\View\View;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;

final class AdminPanelShell extends DefaultPanelShell
{
    public function sidebarBrand(Panel $panel): View
    {
        return view('panels.admin.sidebar-brand', ['panel' => $panel]);
    }

    public function topbarEnd(Panel $panel): View
    {
        return view('panels.admin.topbar-actions', ['panel' => $panel]);
    }
}
```

When extending `DefaultPanelShell`, keep method signatures compatible with its narrowed return types. Use the wider `View|Htmlable|string|null` hook signatures only when extending `PanelShell` directly or when configuring direct panel slot closures.

Available hooks: `sidebarBrand()`, `topbarBrand()`, `mobileSidebarBrand()`, `sidebarFooter()`, `topbarEnd()`, and `mobileHeaderEnd()`.

Prefer extending `DefaultPanelShell` when the app wants package defaults plus local customization. Extend `PanelShell` only when replacing all defaults intentionally.

Package-owned fixed shell icons are replaceable through aliases:

```php
use Zdearo\LivewirePanels\Facades\PanelsIcon;
use Zdearo\LivewirePanels\Icons\PanelsIconAlias;

PanelsIcon::register([
    PanelsIconAlias::SIDEBAR_TOGGLE_BUTTON => 'heroicon-o-queue-list',
    PanelsIconAlias::TOPBAR_GROUP_DROPDOWN_BUTTON => view('icons.chevron-down'),
    PanelsIconAlias::USER_MENU_LOGOUT_BUTTON => 'heroicon-o-arrow-left-start-on-rectangle',
]);
```

String values are Blade Icons names. `Htmlable` values, including Blade views, render directly.

## Authentication

Panel authentication is opt-in. Calling `authenticatables()` adds package authentication middleware and validates the authenticated model class. `authGuard()` only selects the guard used to resolve the current user.

```php
$panel
    ->authGuard('admin')
    ->loginRoute('admin.login')
    ->logoutRoute('admin.logout')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

If `loginRoute()` is omitted, the fallback is `{panelId}.login`, such as `admin.login`. If the selected route does not exist, throw a clear `LogicException` naming the panel and missing route.

The package does not provide login pages. The default Flux user menu renders a logout action only when `logoutRoute()` is configured.

Allowed models may implement `Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel`:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $panel->id === 'admin' && $this->is_admin;
}
```

## Tenancy

Panel tenancy is opt-in and must not imply authentication.

```php
use Zdearo\LivewirePanels\Tenant\Tenant;

$panel
    ->path('admin/{company}')
    ->tenant(Tenant::make(App\Models\Company::class)->routeParameter('company'))
    ->requiresTenant();
```

Tenant route parameters may also come from `subdomain()`:

```php
$panel
    ->subdomain('{company}')
    ->path('admin')
    ->tenant(Tenant::make(App\Models\Company::class)->routeParameter('company'));
```

Tenancy stays model-agnostic. Do not add tenant migrations, tenant base models, global scopes, database-per-tenant behavior, or tenant switcher UI unless that API is discussed first.

Authenticated models may implement `Zdearo\LivewirePanels\Tenant\Contracts\HasPanelTenants` to validate tenant access.

## Facades and URLs

Use the explicit facade import in package docs and examples:

```php
use Zdearo\LivewirePanels\Facades\Panels;

$panel = Panels::currentPanel();
$admin = Panels::panel('admin');
$default = Panels::defaultPanel();
$panels = Panels::panels();
$tenant = Panels::currentTenant();
$url = Panels::route('users');
```

`Panels::route()` prefixes route names with the current panel ID and merges current tenant route parameters before explicit parameters. Explicit parameters win when they use the same key.

## Assets and generator

Configure panel assets through `Panel::vite()` only. Do not add a separate `viteTheme()` API.

The package stylesheet must not import Flux CSS. The consuming app's CSS entrypoint should import Tailwind, Flux CSS from the app vendor directory, then the package stylesheet, and include package Blade views in Tailwind sources.

The package generator is intentionally limited:

```bash
php artisan make:panel admin
php artisan make:panel admin --shell
php artisan make:panel customer-app --path=customers --name=Customers --middleware=web --middleware=auth --default --force
```

The command creates `app/Providers/AdminPanelProvider.php`, `resources/css/panels/admin.css`, optionally `app/Panels/Admin/AdminPanelShell.php`, registers the provider in `bootstrap/providers.php`, and updates `vite.config.js` only when it can safely find a Laravel Vite `input: [...]` array.

Do not add a package page generator. Developers should create Livewire components with Livewire tooling and register them with `Page::make(...)`.

## Translation and lazy values

Panel providers run during Laravel provider registration, so application services such as the translator may not be available yet. Do not call `__()` directly in panel definitions.

Navigation labels are translated by the package when rendered. Use lazy closures for display-time values, not structural routing/config values.

Good lazy values: `Panel::name()`, `Panel::navigationMode()`, navigation labels, navigation URLs, badges, visibility flags, navigation group labels, and shell slots.

## Verification

When changing this package, run the relevant focused Pest test first, then the full validation command before claiming completion:

```bash
vendor/bin/pest tests/Feature/PanelProviderTest.php
composer test
```

`composer test` runs Rector dry-run, Pint in check mode, PHPStan, and Pest with coverage. If the local PHP runtime has no coverage driver, report the observed coverage-driver failure and still run the focused tests that cover the change.

## Common mistakes

- Importing `NavigationMode` from `Zdearo\LivewirePanels\Navigation`; the enum lives in `Zdearo\LivewirePanels\Enums`.
- Importing `Page` from `Zdearo\LivewirePanels\Panel`; it lives in `Zdearo\LivewirePanels\Page`.
- Using a `LivewirePanels` facade; the package facade is `Zdearo\LivewirePanels\Facades\Panels` and the Laravel alias is `Panels`.
- Adding starter-kit concerns such as login pages, dashboards, app models, CRUD resources, or default app folders to the core package.
- Putting navigation items in a sidebar by default. Pages must opt in with `navigation()`.
- Treating page groups as navigation groups. They are route structure only.
- Making routing/config values lazy. Lazy values are for display-time customization.
- Adding compatibility shims, old aliases, or deprecated paths instead of cleanly updating callers.
