<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

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
