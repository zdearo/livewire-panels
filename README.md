# Livewire Panels

Core package for developer-defined multi-panel Laravel applications built with Livewire and Flux-oriented layouts.

This repository contains the reusable panel infrastructure package. The starter kit will install this package and provide the application scaffold, authentication, Flux shell, and example panels.

## Direction

- Panel infrastructure lives in this package.
- Application-specific panels live in the generated Laravel app.
- The public contract is based on panel providers, not a required `app/Panels` directory structure.
- Concrete panel APIs will be designed incrementally before implementation.

## Development

```bash
composer install
composer test
composer format
```

