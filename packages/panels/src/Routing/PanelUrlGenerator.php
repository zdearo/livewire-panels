<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Routing;

use Illuminate\Routing\UrlGenerator;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Tenant\TenantManager;

final readonly class PanelUrlGenerator
{
    public function __construct(
        private TenantManager $tenants,
        private UrlGenerator $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function tenantRouteParameters(Panel $panel): array
    {
        return $this->tenants->routeParameters($panel);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function route(Panel $panel, string $name, array $parameters = [], bool $absolute = true): string
    {
        $name = str_starts_with($name, $panel->id.'.') ? $name : $panel->id.'.'.$name;

        return $this->url->route(
            $name,
            array_merge($this->tenantRouteParameters($panel), $parameters),
            $absolute,
        );
    }
}
