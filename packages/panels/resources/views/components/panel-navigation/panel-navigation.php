<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;
use Zdearo\LivewirePanels\Shell\PanelShell;

return new class extends Component
{
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

        return $panel->navigationMode;
    }

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }

    public function shell(): PanelShell
    {
        $panel = $this->currentPanel();

        if ($panel === null) {
            return app(DefaultPanelShell::class);
        }

        return app($panel->shell);
    }

    public function sidebarBrand(): string
    {
        return $this->renderShellPart('sidebarBrand');
    }

    public function topbarBrand(): string
    {
        return $this->renderShellPart('topbarBrand');
    }

    public function mobileSidebarBrand(): string
    {
        return $this->renderShellPart('mobileSidebarBrand');
    }

    public function sidebarFooter(): string
    {
        return $this->renderShellPart('sidebarFooter');
    }

    public function topbarEnd(): string
    {
        return $this->renderShellPart('topbarEnd');
    }

    public function mobileHeaderEnd(): string
    {
        return $this->renderShellPart('mobileHeaderEnd');
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

    public function activeGroup(): ?NavigationGroup
    {
        foreach ($this->navigationGroups() as $group) {
            if (array_any($group->items, fn (NavigationItem $item): bool => $item->isCurrent())) {
                return $group;
            }
        }

        return null;
    }

    public function groupUrl(NavigationGroup $group): string
    {
        return $group->items[0]->url ?? '#';
    }

    private function renderShellPart(string $part): string
    {
        $panel = $this->currentPanel();

        if ($panel === null) {
            return '';
        }

        $content = match ($part) {
            'sidebarBrand' => $this->shell()->sidebarBrand($panel),
            'topbarBrand' => $this->shell()->topbarBrand($panel),
            'mobileSidebarBrand' => $this->shell()->mobileSidebarBrand($panel),
            'sidebarFooter' => $this->shell()->sidebarFooter($panel),
            'topbarEnd' => $this->shell()->topbarEnd($panel),
            'mobileHeaderEnd' => $this->shell()->mobileHeaderEnd($panel),
            default => null,
        };

        return $this->renderContent($content);
    }

    private function renderContent(View|Htmlable|string|null $content): string
    {
        if ($content instanceof View) {
            return $content->render();
        }

        if ($content instanceof Htmlable) {
            return $content->toHtml();
        }

        return $content ?? '';
    }
};
