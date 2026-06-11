## Livewire Panels

The `zdearo/livewire-panels` package provides developer-defined multi-panel infrastructure for Laravel applications using Livewire 4 and Flux UI.

When a task touches panel providers, panel page registration, navigation, shells, panel authentication, tenancy, panel URLs, shell icons, panel assets, or the `make:panel` generator, consider this package's conventions before changing code.

For API usage, code examples, implementation tips, and verification guidance, load the `livewire-panels-development` Boost skill instead of expanding this upfront guideline.

Keep the package boundary clear: this package owns reusable panel infrastructure; consuming applications and starter kits own login pages, dashboards, application models, and app-specific Livewire components.