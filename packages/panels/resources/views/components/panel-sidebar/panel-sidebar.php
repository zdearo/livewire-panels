<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;

return new class extends Component
{
    public function currentPanel(): ?Panel
    {
        return Panels::currentPanel();
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }

    public function navigationItemUsesSpa(NavigationItem $item): bool
    {
        $panel = $this->currentPanel();

        return $item->usesSpaNavigation($panel instanceof Panel ? $panel->spaNavigation : true);
    }
};
