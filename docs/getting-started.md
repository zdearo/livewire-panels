# Getting Started

Livewire Panels is developer-defined multi-panel infrastructure for Laravel applications using Livewire 4 and Flux UI.

This package is the reusable core for a starter-kit ecosystem. It owns panel registration, routing, page descriptors, navigation contracts, authentication guards, Flux-based panel shells, Vite entrypoints, and generator commands. The consuming Laravel app or starter kit owns actual login pages, user models, dashboards, and app-specific Livewire components.

## Requirements

- PHP 8.4+
- Laravel 13+
- Livewire 4
- Flux UI

## Installation

Install the package:

```bash
composer require zdearo/livewire-panels
```

For local development with a path repository:

```json
{
    "repositories": [
        {
            "name": "livewire-panels",
            "type": "path",
            "url": "../livewire-panels",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

## Creating A Panel

Generate a panel provider and panel CSS entrypoint:

```bash
php artisan make:panel admin
```

Generate a custom shell class too:

```bash
php artisan make:panel admin --shell
```

The command creates:

```txt
app/Providers/AdminPanelProvider.php
resources/css/panels/admin.css
```

With `--shell`, it also creates:

```txt
app/Panels/Admin/AdminPanelShell.php
```

The generated provider is registered in `bootstrap/providers.php` and the panel CSS entrypoint is added to `vite.config.js` when the command can safely update the Vite input array.

## Panel Provider

Panels are defined in PHP providers:

```php
<?php

namespace App\Providers;

use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Enums\NavigationMode;
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
            ->vite('resources/css/panels/admin.css')
            ->navigationMode(NavigationMode::TopbarWithSidebar)
            ->navigationGroups([
                NavigationGroup::make('management')->label('Management')->icon('users'),
            ])
            ->pages([
                Page::make('/', 'pages::admin.dashboard')
                    ->name('dashboard')
                    ->navigation('Dashboard', icon: 'home'),

                Page::make('/users', 'pages::admin.users')
                    ->name('users')
                    ->navigation('Users', icon: 'users', group: 'management'),
            ])
            ->default();
    }
}
```

Panel providers are registered by Laravel's provider mechanism. Do not list panels in a package config file.

## Pages

Pages are descriptors for Livewire page routes. The Livewire component itself remains native Livewire 4 and may be SFC, MFC, or class-based:

```php
Page::make('/users', 'pages::admin.users')
    ->name('users')
    ->navigation('Users', icon: 'users');
```

Page groups share route path and route name prefixes:

```php
Page::group('/settings')
    ->name('settings')
    ->pages([
        Page::make('/', 'pages::admin.settings.index')->name('index'),
        Page::make('/users', 'pages::admin.settings.users')->name('users'),
    ]);
```

Inside an `admin` panel this registers:

```txt
GET /admin/settings       admin.settings.index
GET /admin/settings/users admin.settings.users
```

## Navigation

Pages only appear in navigation when `navigation()` is called. Manual navigation items are also supported:

```php
use Zdearo\LivewirePanels\Navigation\NavigationItem;

$panel->navigation([
    NavigationItem::make('Settings')
        ->url('/admin/settings')
        ->icon('cog-6-tooth')
        ->sort(100),
]);
```

Navigation groups must be declared before items reference them:

```php
use Zdearo\LivewirePanels\Navigation\NavigationGroup;

$panel->navigationGroups([
    NavigationGroup::make('management')
        ->label('Management')
        ->icon('briefcase'),
]);
```

If an item references an undeclared group, the panel throws a `LogicException`.

Panel providers run during Laravel provider registration, before every service is safe to resolve. Do not call `__()` directly while configuring the panel. Navigation labels are translated when rendered, and may also be defined lazily:

```php
Page::make('/', 'pages::admin.dashboard')
    ->name('dashboard')
    ->navigation('Home');

NavigationGroup::make('main')
    ->label(fn (): string => __('Main'));
```

The same lazy pattern is available for display-time values:

```php
$panel
    ->name(fn (): string => __('Admin'))
    ->navigation([
        NavigationItem::make(fn (): string => __('Billing'))
            ->url(fn (): string => route('billing.index', absolute: false))
            ->badge(fn (): int => 12)
            ->visible(fn (): bool => auth()->check()),
    ])
    ->navigationGroups([
        NavigationGroup::make('management')
            ->label(fn (): string => __('Management'))
            ->hidden(fn (): bool => false),
    ]);
```

Do not use lazy values for structural settings such as `path`, `subdomain`, middleware, layouts, Vite entries, guards, or tenant route parameters.

## Navigation Modes

Three Flux navigation modes are available:

```php
use Zdearo\LivewirePanels\Enums\NavigationMode;

$panel->navigationMode(NavigationMode::Sidebar);
$panel->navigationMode(NavigationMode::Topbar);
$panel->navigationMode(NavigationMode::TopbarWithSidebar);
```

## Shell Customization

For full control, point the panel to a shell class:

```php
$panel->shell(AdminPanelShell::class);
```

For focused customization, the panel can override individual shell slots. These values may be strings, views, `Htmlable` objects, `null`, or lazy closures that receive the current panel:

```php
$panel
    ->sidebarBrand(fn (Panel $panel): string => '<strong>'.$panel->displayName().'</strong>')
    ->topbarEnd(view('panels.admin.topbar-actions'));
```

String values are accepted too:

```php
$panel->navigationMode('sidebar');
$panel->navigationMode('topbar');
$panel->navigationMode('topbar-sidebar');
```

`Sidebar` renders the primary Flux sidebar. `Topbar` renders flat items and hover dropdown groups in the topbar. `TopbarWithSidebar` renders groups in the topbar and shows a secondary sidebar for the current page group.

Navigation state is resolved from the original page request during Livewire updates, so active items and groups remain stable while the browser is posting to Livewire's update endpoint.

## Panel Shells

The Flux shell can be customized through a class instead of piling visual options into the provider:

```php
$panel->shell(\App\Panels\Admin\AdminPanelShell::class);
```

Shell classes extend `DefaultPanelShell` when they want to keep package defaults and replace only specific parts:

```php
<?php

namespace App\Panels\Admin;

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
```

Available hooks:

```php
sidebarBrand()
topbarBrand()
mobileSidebarBrand()
sidebarFooter()
topbarEnd()
mobileHeaderEnd()
```

Each hook may return a Blade view, an `Htmlable`, a string, or `null`.

## Authentication

Panel authentication is opt-in. Calling `authenticatables()` makes the panel authenticated automatically:

```php
$panel
    ->authGuard('admin')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

`authGuard()` is optional. If omitted, Laravel's default guard is used.

Allowed models may implement `CanAccessPanel` for panel-specific authorization:

```php
use Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel;
use Zdearo\LivewirePanels\Panel\Panel;

final class Admin extends Authenticatable implements CanAccessPanel
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->id === 'admin' && $this->is_admin;
    }
}
```

Unauthenticated panel requests redirect to the panel login route. If `loginRoute()` is not configured, the fallback is `{panelId}.login`:

```php
$panel
    ->id('admin')
    ->authenticatables([App\Models\Admin::class]);
```

This expects a route named `admin.login`. Override it when needed:

```php
$panel
    ->loginRoute('auth.admin.login')
    ->authenticatables([App\Models\Admin::class]);
```

The package does not ship a login page. The starter kit or consuming app should provide the login UI and route.

The default Flux shell can also render a logout action in the user dropdown. Configure the route name on the panel:

```php
$panel
    ->logoutRoute('admin.logout')
    ->authenticatables([App\Models\Admin::class]);
```

If `logoutRoute()` is omitted, the default user menu only shows the authenticated user's name and email.

## Tenancy

Panel tenancy is opt-in. The package resolves and exposes the current tenant, but it does not create tenant models, migrations, global scopes, or database-per-tenant infrastructure.

Configure a tenant model and route parameter on the panel:

```php
use App\Models\Company;
use Zdearo\LivewirePanels\Tenant\Tenant;

$panel
    ->path('admin/{company}')
    ->tenant(
        Tenant::make(Company::class)
            ->routeParameter('company')
    );
```

The route parameter may live in the path or in a panel subdomain. `subdomain()` uses the host from Laravel's `app.url` config by default:

```php
$panel
    ->subdomain('{company}')
    ->path('admin')
    ->tenant(Tenant::make(Company::class)->routeParameter('company'));
```

Use `requiresTenant()` when the panel should not run without a resolved tenant:

```php
$panel
    ->path('admin/{company}')
    ->tenant(Tenant::make(Company::class)->routeParameter('company'))
    ->requiresTenant();
```

Custom tenant resolution is supported with a resolver class:

```php
use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Tenant\Contracts\ResolvesPanelTenant;
use Zdearo\LivewirePanels\Tenant\Tenant;

final class CompanyTenantResolver implements ResolvesPanelTenant
{
    public function resolve(Panel $panel, Tenant $tenant, Request $request): ?object
    {
        return $request->user()?->companies()->whereSlug($request->route('company'))->first();
    }
}

$panel->tenant(
    Tenant::make(Company::class)
        ->resolver(CompanyTenantResolver::class)
);
```

Authenticated users may implement `HasPanelTenants` to authorize tenant access:

```php
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Tenant\Contracts\HasPanelTenants;

final class User extends Authenticatable implements HasPanelTenants
{
    public function panelTenants(Panel $panel): iterable
    {
        return $this->companies;
    }

    public function canAccessPanelTenant(Panel $panel, object $tenant): bool
    {
        return $this->companies()->whereKey($tenant->getKey())->exists();
    }
}
```

Pages can use the facade instead of manually passing tenant route parameters:

```php
$tenant = Panels::currentTenant();

$url = Panels::route('users');
$editUrl = Panels::route('users.edit', ['user' => $user]);
```

When a page has `name()`, generated navigation URLs prefer named routes and automatically include current tenant route parameters.

## Facade

Use the facade when application code needs to read panel state:

```php
use Zdearo\LivewirePanels\Facades\Panels;

$panel = Panels::currentPanel();
```

Other available calls proxy to the panel manager:

```php
Panels::panel('admin');
Panels::defaultPanel();
Panels::panels();
Panels::setCurrentPanel($panel);
Panels::currentTenant();
Panels::setCurrentTenant($tenant);
Panels::tenantRouteParameters();
Panels::route('users');
```

## CSS And Vite

The generated panel CSS imports Tailwind, Flux, and the package panel stylesheet:

```css
@import 'tailwindcss';
@import '../../../vendor/livewire/flux/dist/flux.css';
@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels.css';

@source '../../../vendor/zdearo/livewire-panels/packages/panels/resources/views/**/*.blade.php';
@source '../../views/**/*.blade.php';
@source '../../js/**/*.js';

@custom-variant dark (&:where(.dark, .dark *));
```

Keep the Flux import in the consuming app stylesheet. The package stylesheet does not import Flux with a relative path because the Flux package lives in the application `vendor` directory when installed through Composer.

## Laravel Boost

This package ships Laravel Boost guidelines at:

```txt
resources/boost/guidelines/core.blade.php
```

When a consuming app installs Laravel Boost and runs:

```bash
php artisan boost:install
```

Boost can load these package guidelines so AI agents understand this package's panel provider, navigation, auth, shell, and generator conventions.

## Development

Install dependencies:

```bash
composer install
```

Run the full validation suite:

```bash
composer test
```

This runs Rector dry-run, Pint, PHPStan, and Pest with coverage.

Focused commands:

```bash
composer test:refactor
composer test:lint
composer test:types
vendor/bin/pest tests/Feature/MakePanelCommandTest.php
```
