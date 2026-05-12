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
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelProvider;

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

- `Panel`: fluent configuration object with PHP 8.4 read access through properties.
- `PanelProvider`: base Laravel service provider for one panel.
- `PanelRegistry`: stores registered panels by ID.
- `LivewirePanelsServiceProvider`: package provider that registers shared package services.

Panels have two layout layers:

- `appLayout`: the outer HTML document shell, defaulting to `livewire-panels::layouts.app`.
- `layout`: the inner panel layout, defaulting to `livewire-panels::layouts.panel`.

The package registers its views as both namespaced views and anonymous Blade components. This allows the panel layout to wrap itself with the app layout through Blade components.

Panel pages use a descriptor object rather than forcing application components to extend a package base class:

```php
use Zdearo\LivewirePanels\Page;

$panel->pages([
    Page::make('/', 'pages::admin.dashboard')->name('dashboard'),
    Page::make('/users', 'pages::admin.users')->name('users'),
]);
```

The `Page` object is only a route descriptor. The Livewire component remains native Livewire 4 and may be SFC, MFC, or class-based.

Panel page routes are registered as Livewire page routes and include package middleware that sets the current panel and configures Livewire's page layout to the panel layout.

`PanelRegistry::get()` accepts an optional ID. When no ID is provided or the ID is not found, it falls back to `PanelRegistry::getDefault()`.

Panels can be marked as default with:

```php
$panel->default();
```

`PanelRegistry` supports non-strict lookup for normalized IDs, so values like `sales-panel`, `sales_panel`, and `salespanel` can resolve to the same panel when strict lookup is disabled.

Do not add routing, navigation, layout, Livewire page registration, resources, CRUD builders, current-panel resolution, or additional commands until the API is discussed first.

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

It also registers the provider in Laravel's `bootstrap/providers.php`.

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

The command should only scaffold the panel provider. It must not create app pages, auth, CRUD resources, Flux layouts, or final starter-kit structure.

Generator output should use package stubs. The panel provider template lives at:

```txt
packages/panels/stubs/panel-provider.stub
```

Panel pages are generated through:

```bash
php artisan make:panel-page admin.dashboard --path=/ --name=dashboard --sfc
```

This command delegates component creation to Livewire's `make:livewire` using the `pages::` namespace, then prints the `Page::make(...)` registration snippet for the panel provider.

It supports Livewire's page component modes and options:

```bash
php artisan make:panel-page reports.index --mfc
php artisan make:panel-page reports.index --class
php artisan make:panel-page reports.index --type=sfc --emoji=false
```

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

Composer autoload maps the public panel API from `Zdearo\LivewirePanels\` to `packages/panels/src`, and reserves `Zdearo\LivewirePanels\Support\` for shared support classes in `packages/support/src`.

## Dependency Notes

The project currently uses Laravel 13, Livewire 4, Orchestra Testbench 11, Pest 4, and Pint.

If `composer validate --strict` reports the ignored `composer.lock` is stale after changing `composer.json`, update the local lock with:

```bash
composer update --lock
```

The lock file is local/ignored at the moment, so do not treat lock updates as a package source change unless the ignore policy changes.
