<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

return new class extends Component
{
    private ?NavigationContract $resolvedNavigationContract = null;

    public function currentPanel(): ?Panel
    {
        return Panels::currentPanel();
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->resolvedNavigationContract ??= $this->currentPanel()?->navigationContract();
    }

    public function navigationItemUsesSpa(NavigationItem $item): bool
    {
        $panel = $this->currentPanel();

        return $item->usesSpaNavigation($panel instanceof Panel ? $panel->spaNavigation : true);
    }

    public function navigationItemIsCurrent(NavigationItem $item): bool
    {
        return $this->navigationContract()?->itemIsCurrentFor(
            $item,
            app(CurrentRequestResolver::class)->resolve(request()),
            $this->currentPanel(),
        ) ?? false;
    }
};
