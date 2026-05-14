<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant;

use Zdearo\LivewirePanels\Panel\Panel;

final class TenantManager
{
    public private(set) ?object $currentTenant = null;

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

        if ($panel->tenant === null || $panel->tenant->routeParameter === null || $tenant === null) {
            return [];
        }

        return [
            $panel->tenant->routeParameter => $this->routeKey($tenant),
        ];
    }

    private function routeKey(object $tenant): mixed
    {
        if (method_exists($tenant, 'getRouteKey')) {
            return $tenant->getRouteKey();
        }

        return $tenant;
    }
}
