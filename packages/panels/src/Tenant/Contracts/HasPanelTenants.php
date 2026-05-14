<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant\Contracts;

use Zdearo\LivewirePanels\Panel\Panel;

interface HasPanelTenants
{
    /**
     * @return iterable<int, object>
     */
    public function panelTenants(Panel $panel): iterable;

    public function canAccessPanelTenant(Panel $panel, object $tenant): bool;
}
