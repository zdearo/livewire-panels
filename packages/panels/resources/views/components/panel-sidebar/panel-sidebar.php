<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\Facades\LivewirePanels;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Panel\Panel;

return new class extends Component
{
    public function currentPanel(): ?Panel
    {
        return LivewirePanels::currentPanel();
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }
};
