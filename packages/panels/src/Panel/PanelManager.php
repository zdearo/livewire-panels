<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

final class PanelManager
{
    private ?Panel $currentPanel = null;

    public function __construct(
        private readonly PanelRegistry $registry,
    ) {}

    public function setCurrentPanel(Panel $panel): void
    {
        $this->currentPanel = $panel;
    }

    public function getCurrentPanel(): ?Panel
    {
        return $this->currentPanel;
    }

    public function panel(?string $id = null, bool $isStrict = true): Panel
    {
        return $this->registry->get($id, $isStrict);
    }
}
