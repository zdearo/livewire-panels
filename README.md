# Livewire Panels

Developer-defined multi-panel infrastructure for Laravel applications using Livewire 4 and Flux UI.

This package is the reusable core for a starter-kit ecosystem. It owns panel registration, routing, page descriptors, navigation contracts, authentication guards, Flux-based panel shells, Vite entrypoints, and generator commands. The consuming Laravel app or starter kit owns login pages, user models, dashboards, and app-specific Livewire components.

## Quick Start

Install the package:

```bash
composer require zdearo/livewire-panels
```

Create a panel:

```bash
php artisan make:panel admin
```

Create a panel with a custom shell class:

```bash
php artisan make:panel admin --shell
```

## Documentation

The full usage guide lives in [docs/getting-started.md](docs/getting-started.md).

It covers:

- installation;
- panel providers;
- pages and page groups;
- navigation groups and navigation modes;
- panel shell customization;
- authentication and login routes;
- the `LivewirePanels` facade;
- CSS and Vite setup;
- Laravel Boost guidelines.

## Laravel Boost

This package ships Laravel Boost guidelines at [resources/boost/guidelines/core.blade.php](resources/boost/guidelines/core.blade.php). Consuming apps that use Laravel Boost can load these guidelines when running:

```bash
php artisan boost:install
```

## Development

Run the full package validation suite:

```bash
composer test
```

This runs Rector dry-run, Pint, PHPStan, and Pest with coverage.
