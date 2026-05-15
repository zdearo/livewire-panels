<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant;

use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

final class TenantManager
{
    public private(set) ?object $currentTenant = null;

    public function __construct(
        private readonly CurrentRequestResolver $requests,
    ) {}

    public function setCurrentTenant(?object $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function currentTenant(): ?object
    {
        return $this->currentTenant;
    }

    /**
     * @return array<string, mixed>
     */
    public function routeParameters(Panel $panel, ?object $tenant = null): array
    {
        $tenant ??= $this->currentTenant;

        if ($panel->tenant === null || $panel->tenant->routeParameter === null) {
            return [];
        }

        $routeParameter = $panel->tenant->routeParameter;
        $routeValue = $tenant ?? $this->currentRouteTenantParameter($routeParameter);

        if ($routeValue === null) {
            return [];
        }

        return [
            $routeParameter => $this->routeKey($routeValue),
        ];
    }

    private function currentRouteTenantParameter(string $routeParameter): mixed
    {
        return $this->requests->resolve(request())->route($routeParameter);
    }

    private function routeKey(mixed $tenant): mixed
    {
        if (is_object($tenant) && method_exists($tenant, 'getRouteKey')) {
            return $tenant->getRouteKey();
        }

        return $tenant;
    }
}
