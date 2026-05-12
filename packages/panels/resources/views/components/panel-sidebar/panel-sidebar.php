<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\NavigationContract;
use Zdearo\LivewirePanels\Panel;
use Zdearo\LivewirePanels\PanelManager;

return new class extends Component
{
    public function currentPanel(): ?Panel
    {
        return app(PanelManager::class)->getCurrentPanel();
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }
};
