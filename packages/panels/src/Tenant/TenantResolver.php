<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant;

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Tenant\Contracts\ResolvesPanelTenant;

final class TenantResolver implements ResolvesPanelTenant
{
    public function resolve(Panel $panel, Tenant $tenant, Request $request): ?object
    {
        if ($tenant->routeParameter === null) {
            return null;
        }

        $request = $this->originalRequest($request);
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

    private function originalRequest(Request $request): Request
    {
        if (! $request->is('livewire/*') || ! app()->bound('originalRequest')) {
            return $request;
        }

        $originalRequest = app('originalRequest');

        return $originalRequest instanceof Request ? $originalRequest : $request;
    }
}
