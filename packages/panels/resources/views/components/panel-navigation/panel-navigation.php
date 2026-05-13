<?php

declare(strict_types=1);

use Livewire\Component;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Navigation\NavigationMode;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

return new class extends Component
{
    public ?string $activeGroupId = null;

    public function mount(): void
    {
        $this->activeGroupId = $this->firstGroup()?->id;
    }

    public function currentPanel(): ?Panel
    {
        return app(PanelManager::class)->getCurrentPanel();
    }

    public function navigationMode(): NavigationMode
    {
        $panel = $this->currentPanel();

        if ($panel === null) {
            return NavigationMode::Sidebar;
        }

        return $panel->navigationMode;
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function navigationItems(): array
    {
        return $this->navigationContract()?->items() ?? [];
    }

    /**
     * @return array<int, NavigationGroup>
     */
    public function navigationGroups(): array
    {
        return $this->navigationContract()?->groups() ?? [];
    }

    public function setActiveGroup(string $groupId): void
    {
        foreach ($this->navigationGroups() as $group) {
            if ($group->id === $groupId) {
                $this->activeGroupId = $groupId;

                return;
            }
        }
    }

    public function activeGroup(): ?NavigationGroup
    {
        foreach ($this->navigationGroups() as $group) {
            if ($group->id === $this->activeGroupId) {
                return $group;
            }
        }

        $group = $this->firstGroup();
        $this->activeGroupId = $group?->id;

        return $group;
    }

    private function firstGroup(): ?NavigationGroup
    {
        return $this->navigationGroups()[0] ?? null;
    }
};
