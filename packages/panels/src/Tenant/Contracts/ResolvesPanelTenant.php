<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant\Contracts;

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Tenant\Tenant;

interface ResolvesPanelTenant
{
    public function resolve(Panel $panel, Tenant $tenant, Request $request): ?object;
}
