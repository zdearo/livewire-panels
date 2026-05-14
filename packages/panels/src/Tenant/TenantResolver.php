<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant;

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;
use Zdearo\LivewirePanels\Tenant\Contracts\ResolvesPanelTenant;

final readonly class TenantResolver implements ResolvesPanelTenant
{
    public function __construct(
        private CurrentRequestResolver $requests,
    ) {}

    public function resolve(Panel $panel, Tenant $tenant, Request $request): ?object
    {
        if ($tenant->routeParameter === null) {
            return null;
        }

        $request = $this->requests->resolve($request);
        $value = $request->route($tenant->routeParameter);

        if ($value instanceof $tenant->model) {
            return $value;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $model = new $tenant->model;

        if (! method_exists($model, 'resolveRouteBinding')) {
            return null;
        }

        $resolved = $model->resolveRouteBinding($value);

        return $resolved instanceof $tenant->model ? $resolved : null;
    }
}
