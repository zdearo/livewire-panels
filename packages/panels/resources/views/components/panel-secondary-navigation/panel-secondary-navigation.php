<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Livewire\Attributes\On;
use Livewire\Component;
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

return new class extends Component
{
    public string $currentPath = '';

    public function mount(): void
    {
        if ($this->currentPath === '') {
            $this->currentPath = app(CurrentRequestResolver::class)
                ->resolve(request())
                ->path();
        }
    }

    public function currentPanel(): ?Panel
    {
        return Panels::currentPanel();
    }

    public function navigationMode(): NavigationMode
    {
        $panel = $this->currentPanel();

        if ($panel === null) {
            return NavigationMode::Sidebar;
        }

        return $panel->displayNavigationMode();
    }

    #[On('livewire-panels::refresh-navigation')]
    public function refreshNavigation(): void
    {
        //
    }

    public function activeGroup(): ?NavigationGroup
    {
        $navigation = $this->currentPanel()?->navigationContract();

        if ($navigation === null) {
            return null;
        }

        foreach ($navigation->groups() as $group) {
            if (array_any($group->items, fn (NavigationItem $item): bool => $this->navigationItemIsCurrent($item))) {
                return $group;
            }
        }

        return null;
    }

    public function navigationItemIsCurrent(NavigationItem $item): bool
    {
        return $item->isCurrentFor($this->currentRequest(), $this->currentPanel());
    }

    public function navigationItemUsesSpa(NavigationItem $item): bool
    {
        $panel = $this->currentPanel();

        return $item->usesSpaNavigation($panel instanceof Panel ? $panel->spaNavigation : true);
    }

    private function currentRequest(): Request
    {
        if ($this->currentPath === '') {
            return app(CurrentRequestResolver::class)->resolve(request());
        }

        return Request::create('/'.ltrim($this->currentPath, '/'));
    }
};
