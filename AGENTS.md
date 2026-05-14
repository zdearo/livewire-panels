# AGENTS.md

## Project Context

This directory is the core package for a Laravel/Livewire multi-panel starter ecosystem.

The parent workspace may later contain more projects, including the actual Laravel starter kit. This package should remain focused on reusable panel infrastructure only.

Current package path:

```txt
/home/zdearo/Projects/Coding/packages/livewire-starter-kit/livewire-panels
```

## Product Direction

The system is split into two artifacts:

- A reusable core package, currently in this directory.
- A Laravel starter kit that will install the core package and provide the app scaffold.

The core package owns infrastructure such as panel objects, panel providers, registries, routing integration, navigation primitives, and future commands.

The starter kit owns app-specific scaffolding such as authentication, Flux layout, sample panels, sample pages, and initial app structure.

Do not put starter-app concerns into this package unless explicitly requested.

## Naming

The PHP namespace is:

```php
Zdearo\LivewirePanels
```

The Composer package name is not final yet. The current package name may be temporary and should not be treated as a product decision.

## Current Architectural Decisions

- Panels are defined by developers in PHP.
- Panels are not created by end users in the database.
- Panels are not listed in this package's config file.
- The package must not require a fixed app structure such as `app/Panels/...`.
- The public contract is based on one provider per panel.
- Panel registration is automatic when a panel provider is registered by Laravel.
- The app should register its panel providers using Laravel's provider mechanism, such as `bootstrap/providers.php`.
- The core package should stay independent from the final starter kit folder layout.

## Current API Shape

A consumer-facing panel provider should look like this:

```php
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
            ->middleware(['web', 'auth'])
            ->default();
    }
}
```

`PanelProvider::register()` is final and handles automatic registration in `PanelRegistry`.

Do not require each panel provider to manually resolve or call the registry.

Application code may use the facade instead of resolving `PanelManager` manually:

```php
use Zdearo\LivewirePanels\Facades\Panels;

$panel = Panels::currentPanel();
$admin = Panels::panel('admin');
$default = Panels::defaultPanel();
$panels = Panels::panels();
```

The package also registers the Laravel alias `Panels`, but explicit imports are still preferred in package docs and tests.

## PHP Version And Properties

The package targets PHP 8.4+.

Use PHP 8.4 asymmetric property visibility for panel state where it improves clarity:

```php
public private(set) string $id;
```

The convention is:

- Fluent methods are used for writing configuration: `$panel->id('admin')`.
- Public readonly-from-outside properties are used for reading configuration: `$panel->id`.
- Avoid paired getters such as `getId()` unless there is a specific reason.

External mutation of panel state should not be allowed:

```php
$panel->id = 'app'; // should fail outside the class
```

## Current Core Classes

The first implementation layer is intentionally small:

- `Panel\Panel`: fluent configuration object with PHP 8.4 read access through properties.
- `Panel\PanelProvider`: base Laravel service provider for one panel.
- `Panel\PanelRegistry`: stores registered panels by ID.
- `Panel\PanelManager`: tracks the current panel.
- `Page\Page`: route descriptor for Livewire page routes.
- `Page\PageGroup`: structural page group for sharing route path and route name prefixes.
- `Facades\Panels`: Laravel facade for resolving panels, listing panels, and reading or setting the current panel through `PanelManager`.
- `Auth\Contracts\CanAccessPanel`: optional model contract for panel-specific access checks.
- `Navigation\NavigationBuilder`: builds the normalized navigation contract from panel navigation items and page descriptors.
- `Navigation\NavigationItem`, `Navigation\NavigationGroup`, and `Navigation\NavigationContract`: normalized panel navigation primitives.
- `Enums\NavigationMode`: Flux navigation layout mode.
- `Tenant\Tenant`: fluent tenant configuration object for panels.
- `Tenant\TenantManager`: tracks the current resolved tenant and tenant route parameters.
- `Tenant\TenantResolver`: default route-parameter tenant resolver.
- `Tenant\Contracts\HasPanelTenants`: optional authenticated model contract for tenant access checks.
- `Tenant\Contracts\ResolvesPanelTenant`: custom tenant resolver contract.
- `Middleware\SetCurrentTenant`: resolves and stores the current panel tenant for each panel request.
- `Routing\PanelRouter`: converts panel pages and panel route callbacks into Laravel routes.
- `Routing\PanelUrlGenerator`: generates panel route URLs with current tenant parameters.
- `Support\Http\CurrentRequestResolver`: resolves the effective page request, using `originalRequest` only during Livewire update requests.
- `Support\Routing\RouteSegments`: joins route path and route name prefixes consistently across page registration and navigation building.
- `LivewirePanelsServiceProvider`: the only root-level package provider in `packages/panels/src`.

Keep `packages/panels/src` organized by domain. The root of `src` should only contain package-level entry points that genuinely sit above a single domain, such as `LivewirePanelsServiceProvider`.

Panels have two layout layers:

- `appLayout`: the outer HTML document shell, defaulting to `livewire-panels::layouts.app`.
- `layout`: the inner panel layout, defaulting to `livewire-panels::layouts.panel`.

The package registers its views as both namespaced views and anonymous Blade components. This allows the panel layout to wrap itself with the app layout through Blade components.

The package app layout does not assume fixed Vite entrypoint names such as `resources/css/app.css`.

Each panel can define the Vite entrypoints that should be loaded for that panel:

```php
$panel->vite(['resources/css/app.css', 'resources/js/app.js']);
```

The package ships a panel stylesheet source that should be imported by the consuming app's chosen CSS entrypoint:

```css
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels.css';
```

The package stylesheet must not import Flux's CSS with a package-relative path. When the package is installed in an application, `vendor/livewire/flux` belongs to the consuming application's vendor directory. The generated app stylesheet should import Flux before the package panel stylesheet.

The consuming app's Tailwind source list must include the package Blade views so Tailwind sees classes used by the default Flux panel shell:

```css
@source '../../vendor/zdearo/livewire-panels/packages/panels/resources/views/**/*.blade.php';
```

Panel assets are configured through a single `vite()` entrypoint list. Add panel-specific CSS or JavaScript there when needed:

```php
$panel->vite([
    'resources/css/panels/admin.css',
    'resources/js/panels/admin.js',
]);
```

The package does not expose a separate `viteTheme()` API.

Panel authentication is opt-in. A panel without `authenticatables()` must remain public unless the developer manually adds auth middleware:

```php
$panel
    ->id('public')
    ->path('public');
```

Calling `authenticatables()` makes the panel authenticated automatically. The package adds its panel authentication middleware to the panel routes and validates the current authenticated model:

```php
$panel
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

`authGuard()` is optional and only defines which Laravel guard should be used to resolve the current user. If it is omitted, Laravel's default guard is used:

```php
$panel
    ->authGuard('admin')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

When an authenticated panel receives an unauthenticated request, the package redirects to the panel login route. If `loginRoute()` is not configured, the conventional fallback is `{panelId}.login`:

```php
$panel
    ->id('admin')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

This expects a route named `admin.login`. A panel may override it:

```php
$panel
    ->loginRoute('auth.admin.login')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

If the selected route does not exist, the package must throw a clear `LogicException` naming the panel and missing route. The package should not provide a default login page; that belongs to the starter kit or consuming app.

Authenticated panels may configure a logout route for the default Flux user menu. If `logoutRoute()` is omitted, the default user menu only renders the authenticated user's name and email:

```php
$panel
    ->logoutRoute('admin.logout')
    ->authenticatables([
        App\Models\Admin::class,
    ]);
```

Allowed models may implement `Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel` for panel-specific rules:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $panel->id === 'admin' && $this->is_admin;
}
```

Panel tenancy is opt-in. Calling `tenant()` configures how the panel resolves a tenant; it must not imply authentication. Authentication remains controlled by `authenticatables()`:

```php
use Zdearo\LivewirePanels\Tenant\Tenant;

$panel
    ->path('admin/{company}')
    ->tenant(Tenant::make(App\Models\Company::class)->routeParameter('company'));
```

Tenant route parameters may also come from a configured panel subdomain. `subdomain()` prefixes the host from Laravel's `app.url` config:

```php
$panel
    ->subdomain('{company}')
    ->path('admin')
    ->tenant(Tenant::make(App\Models\Company::class)->routeParameter('company'));
```

Calling `requiresTenant()` makes a missing tenant a request error:

```php
$panel
    ->tenant(Tenant::make(App\Models\Company::class)->routeParameter('company'))
    ->requiresTenant();
```

Tenancy must stay model-agnostic. The package resolves and exposes the current tenant, but it must not create tenant migrations, model base classes, global scopes, database-per-tenant behavior, or a tenant switcher UI unless those APIs are discussed first.

Authenticated models may implement `Zdearo\LivewirePanels\Tenant\Contracts\HasPanelTenants` to validate access to the resolved tenant:

```php
public function panelTenants(Panel $panel): iterable
{
    return $this->companies;
}

public function canAccessPanelTenant(Panel $panel, object $tenant): bool
{
    return $this->companies()->whereKey($tenant->getKey())->exists();
}
```

Application code may read the current tenant through the existing facade:

```php
$tenant = Panels::currentTenant();
$url = Panels::route('users');
```

`Panels::route()` prefixes route names with the current panel ID and merges current tenant route parameters before explicit parameters. Explicit parameters should win when they use the same key.

Panel pages use a descriptor object rather than forcing application components to extend a package base class:

```php
use Zdearo\LivewirePanels\Page\Page;

$panel->pages([
    Page::make('/', 'pages::admin.dashboard')->name('dashboard'),
    Page::make('/users', 'pages::admin.users')->name('users'),
]);
```

The `Page` object is only a route descriptor. The Livewire component remains native Livewire 4 and may be SFC, MFC, or class-based.

Page groups are structural route groups, not sidebar navigation groups:

```php
Page::group('/settings')
    ->name('settings')
    ->pages([
        Page::make('/', 'pages::admin.settings.index')->name('index'),
        Page::make('/users', 'pages::admin.settings.users')->name('users'),
    ]);
```

Inside an `admin` panel, this registers `/admin/settings` as `admin.settings.index` and `/admin/settings/users` as `admin.settings.users`.

Panel page routes are registered as Livewire page routes and include package middleware that sets the current panel and configures Livewire's page layout to the panel layout.

The package binds `originalRequest` as a scoped container entry. Outside Livewire update requests it returns the current request. During Livewire update requests it uses `Support\Http\OriginalRequestResolver`, backed by Livewire's `PersistentMiddleware` internals, to rebuild the original page request and resolve its route.

Code that needs to know the active page URL during Livewire updates should use `Support\Http\CurrentRequestResolver` instead of reading `request()` or `originalRequest` directly. Navigation current-state detection and tenant resolution use this resolver so active items, active groups, and tenant route parameters still match the page route while the browser is posting to Livewire's update endpoint.

Panel pages do not appear in sidebar navigation by default. A page must opt in with `navigation()`:

```php
Page::make('/users', 'pages::admin.users')
    ->name('users')
    ->navigation('Users', icon: 'users', group: 'management', sort: 20);
```

Panel providers run during Laravel provider registration, so application services such as the translator may not be available yet. Do not call `__()` directly in panel definitions. Navigation labels are translated by the package when rendered, and labels may be lazy closures when dynamic translation is needed:

```php
Page::make('/', 'pages::admin.dashboard')
    ->navigation('Home');

NavigationGroup::make('main')
    ->label(fn (): string => __('Main'));
```

The package resolves lazy values through `Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures`. Keep this for display-time/customization values, not structural routing/config values.

Good lazy candidates currently supported:

- `Panel::name()`, resolved through `displayName()`.
- `Panel::navigationMode()`, resolved through `displayNavigationMode()` during navigation rendering.
- `NavigationItem::make()`, `url()`, `badge()`, `visible()`, and `hidden()`.
- `NavigationGroup::label()`, `visible()`, and `hidden()`.
- Panel shell slot overrides: `sidebarBrand()`, `topbarBrand()`, `mobileSidebarBrand()`, `sidebarFooter()`, `topbarEnd()`, and `mobileHeaderEnd()`.

Do not make `path`, `subdomain`, middleware, layouts, Vite entries, auth guards, login route names, tenant route parameters, or sort values lazy unless the routing/config lifecycle is discussed again.

Navigation item groups must be declared explicitly on the panel before any item references them. Group references use the group ID, not the visible label:

```php
use Zdearo\LivewirePanels\Navigation\NavigationGroup;

$panel->navigationGroups([
    NavigationGroup::make('management')
        ->label('Management')
        ->icon('briefcase')
        ->sort(20),
]);
```

If a page or manual item references a group ID that was not declared, the panel must throw a `LogicException` when building its navigation contract.

Panels may also define manual navigation items:

```php
use Zdearo\LivewirePanels\Navigation\NavigationItem;

$panel->navigation([
    NavigationItem::make('Settings')
        ->url('/admin/settings')
        ->icon('cog-6-tooth')
        ->sort(100),
]);
```

The panel emits a normalized navigation contract through `navigationContract()`. The default panel sidebar must render from that contract. If no navigation is configured, it must not render demo items.

Panels support three navigation modes:

```php
use Zdearo\LivewirePanels\Enums\NavigationMode;

$panel->navigationMode(NavigationMode::Sidebar);
$panel->navigationMode(NavigationMode::Topbar);
$panel->navigationMode(NavigationMode::TopbarWithSidebar);
```

String values are also accepted:

```php
$panel->navigationMode('sidebar');
$panel->navigationMode('topbar');
$panel->navigationMode('topbar-sidebar');
```

Lazy values are accepted for request/user/tenant-specific presentation decisions:

```php
$panel->navigationMode(fn (): NavigationMode => auth()->user()?->prefers_topbar
    ? NavigationMode::Topbar
    : NavigationMode::Sidebar);
```

When a lazy navigation mode depends on state changed during an active Livewire session, the app may dispatch `livewire-panels::refresh-navigation`. The event must not carry the target mode. It only asks the package navigation component to re-render and resolve `Panel::displayNavigationMode()` again:

```php
$this->dispatch('livewire-panels::refresh-navigation');
```

`Sidebar` is the default mode and preserves the original sidebar shell.

`Topbar` renders flat navigation items in the topbar. Navigation groups render as hover dropdowns in the topbar.

`TopbarWithSidebar` renders flat navigation items and group labels in the topbar. Each group label links to the first item in that group, so clicking a group navigates to its first page. The secondary sidebar renders only when the current page belongs to a navigation group, and it shows the items from that current page group. If the current page is a flat item with no group, the secondary sidebar is hidden.

Navigation group hover must not trigger Livewire requests. Only the `Topbar` mode uses hover dropdowns, and that hover is handled by Flux locally. Navigation happens only when the user clicks an actual page/item link.

For mobile, topbar modes render a collapsible sidebar containing the full navigation contract.

The Flux navigation shell is customizable through a shell class, not by adding many visual options directly to the panel provider. The provider should only point to the class:

```php
$panel->shell(AdminPanelShell::class);
```

Shell classes extend `Zdearo\LivewirePanels\Shell\PanelShell` and may override focused render hooks such as `sidebarBrand()`, `topbarBrand()`, `mobileSidebarBrand()`, `sidebarFooter()`, `topbarEnd()`, and `mobileHeaderEnd()`. Hooks may return a Blade view, an `Htmlable`, a string, or `null`.

Panels may also override those same shell slots directly for focused customization. Direct slot values may be lazy closures that receive the current `Panel`. A configured slot takes precedence over the shell class for that slot.

The default shell uses Flux patterns and the panel name for the brand. When a panel has `authenticatables()` configured and a user is authenticated, the default shell renders a Flux user dropdown in the sidebar/topbar header locations.

The default panel navigation shell is a Livewire 4 multi-file component, not a class component. Keep it in:

```txt
packages/panels/resources/views/components/panel-navigation/panel-navigation.php
packages/panels/resources/views/components/panel-navigation/panel-navigation.blade.php
```

The older sidebar-only component remains available in:

```txt
packages/panels/resources/views/components/panel-sidebar/panel-sidebar.php
packages/panels/resources/views/components/panel-sidebar/panel-sidebar.blade.php
```

The package registers the Livewire namespace `livewire-panels` against `packages/panels/resources/views/components`, and the panel layout wraps the page slot with:

```blade
<livewire:livewire-panels::panel-navigation>
    {{ $slot }}
</livewire:livewire-panels::panel-navigation>
```

`PanelRegistry::get()` accepts an optional ID. When no ID is provided or the ID is not found, it falls back to `PanelRegistry::getDefault()`.

Panels can be marked as default with:

```php
$panel->default();
```

`PanelRegistry` supports non-strict lookup for normalized IDs, so values like `sales-panel`, `sales_panel`, and `salespanel` can resolve to the same panel when strict lookup is disabled.

Do not add additional routing, navigation, layout, Livewire page registration, resources, CRUD builders, current-panel resolution, or commands until the API is discussed first.

## Artisan Commands

The core package exposes one app-facing generator command:

```bash
php artisan make:panel admin
```

If the `id` argument is omitted, the command asks for it interactively:

```bash
php artisan make:panel
```

This command creates an app panel provider at:

```txt
app/Providers/AdminPanelProvider.php
```

It also creates a panel CSS entrypoint at:

```txt
resources/css/panels/admin.css
```

The generated provider must reference that entrypoint with:

```php
->vite('resources/css/panels/admin.css')
```

The generated CSS file imports Tailwind, Flux CSS from the consuming application's `vendor`, and then the package panel stylesheet.

The command should also add that CSS entrypoint to `vite.config.js` when it can safely find a Laravel Vite `input: [...]` array. If the file is missing or the input shape is not recognized, the command should warn the developer to add the entrypoint manually instead of failing.

The command also registers the provider in Laravel's `bootstrap/providers.php`.

The first generated panel is marked as default automatically. Additional panels are not marked as default unless the developer passes `--default`.

Supported options:

```bash
php artisan make:panel customer-app \
    --path=customers \
    --name=Customers \
    --middleware=web \
    --middleware=auth \
    --default \
    --force
```

The command should only scaffold the panel provider and its panel CSS entrypoint. It must not create app pages, auth, CRUD resources, Flux layouts beyond the package defaults, or final starter-kit structure.

The command may optionally scaffold a panel shell class:

```bash
php artisan make:panel admin --shell
```

When `id` is omitted, the command asks whether to create the shell class interactively. Shell classes are created at:

```txt
app/Panels/Admin/AdminPanelShell.php
```

When a shell is generated, the panel provider must import it and call:

```php
->shell(AdminPanelShell::class)
```

Generator output should use package stubs. The panel provider template lives at:

```txt
packages/panels/stubs/panel-provider.stub
```

The panel CSS template lives at:

```txt
packages/panels/stubs/panel.css.stub
```

The panel shell template lives at:

```txt
packages/panels/stubs/panel-shell.stub
```

The package must not expose a page generator command. Developers should create Livewire page components with Livewire's own generator and register them in panel providers with `Page::make(...)`.

The package-owned generator surface is intentionally limited to `php artisan make:panel`.

## Testing

Use Pest with Orchestra Testbench.

The main validation command is:

```bash
composer test
```

This runs Rector dry-run, Pint in check mode, PHPStan, and then Pest with coverage in parallel.

PHPStan is configured in `phpstan.neon.dist` and currently analyzes `packages/`.

Because `composer test` runs Pest with `--coverage`, the local PHP runtime must have a coverage driver such as Xdebug or PCOV enabled. If neither is available, `composer test:unit` will fail with "No code coverage driver is available" before running tests.

Useful focused command:

```bash
vendor/bin/pest tests/Feature/PanelProviderTest.php
```

Useful static-analysis command:

```bash
composer test:types
```

Useful refactor check command:

```bash
composer test:refactor
```

Useful lint check command:

```bash
composer test:lint
```

Before saying work is complete, run the relevant focused test and `composer test`.

## Formatting

Use Pint:

```bash
composer lint
```

Do not hand-format around Pint when Pint can do the mechanical work.

Use Rector for automated refactors:

```bash
composer refactor
```

## Git And Commit Rules

The user explicitly requested that no further commits be made for now.

Do not commit unless the user explicitly asks again.

The repository is inside `livewire-panels/`, not the parent `livewire-starter-kit/` directory.

The repo root now acts as a small internal monorepo. Runtime package code lives under:

```txt
packages/panels/src
packages/support/src
```

Composer autoload maps the public panel API from `Zdearo\LivewirePanels\` to `packages/panels/src`, and reserves `Zdearo\LivewirePanels\Support\` for shared support classes in `packages/support/src`. Code in `packages/panels/src` should prefer support utilities for cross-domain concerns such as effective request resolution and route segment joining.

## Dependency Notes

The project currently uses Laravel 13, Livewire 4, Orchestra Testbench 11, Pest 4, and Pint.

If `composer validate --strict` reports the ignored `composer.lock` is stale after changing `composer.json`, update the local lock with:

```bash
composer update --lock
```

The lock file is local/ignored at the moment, so do not treat lock updates as a package source change unless the ignore policy changes.
