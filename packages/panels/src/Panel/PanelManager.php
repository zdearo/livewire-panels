<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Zdearo\LivewirePanels\Routing\PanelUrlGenerator;
use Zdearo\LivewirePanels\Tenant\TenantManager;

final class PanelManager
{
    public private(set) ?Panel $currentPanel = null;

    public function __construct(
        private readonly PanelRegistry $registry,
        private readonly TenantManager $tenants,
        private readonly PanelUrlGenerator $urls,
    ) {}

    public function setCurrentPanel(Panel $panel): void
    {
        $this->currentPanel = $panel;
    }

    public function currentPanel(): ?Panel
    {
        return $this->currentPanel;
    }

    public function panel(?string $id = null, bool $isStrict = true): Panel
    {
        return $this->registry->get($id, $isStrict);
    }

    public function defaultPanel(): Panel
    {
        return $this->registry->getDefault();
    }

    /**
     * @return array<string, Panel>
     */
    public function panels(): array
    {
        return $this->registry->all();
    }

    public function setCurrentTenant(?object $tenant): void
    {
        $this->tenants->setCurrentTenant($tenant);
    }

    public function currentTenant(): ?object
    {
        return $this->tenants->currentTenant();
    }

    /**
     * @return array<string, mixed>
     */
    public function tenantRouteParameters(?Panel $panel = null): array
    {
        return $this->urls->tenantRouteParameters($panel ?? $this->panel());
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function route(string $name, array $parameters = [], bool $absolute = true, ?Panel $panel = null): string
    {
        $panel ??= $this->currentPanel() ?? $this->panel();

        return $this->urls->route($panel, $name, $parameters, $absolute);
    }
}
