<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Livewire\Attributes\On;
use Livewire\Component;
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;
use Zdearo\LivewirePanels\Shell\PanelShell;
use Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

return new class extends Component
{
    use EvaluatesClosures;

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

    public function navigationContract(): ?NavigationContract
    {
        return $this->currentPanel()?->navigationContract();
    }

    #[On('livewire-panels::refresh-navigation')]
    public function refreshNavigation(): void
    {
        //
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
            if (array_any($group->items, fn (NavigationItem $item): bool => $this->navigationItemIsCurrent($item))) {
                return $group;
            }
        }

        return null;
    }

    public function navigationItemIsCurrent(NavigationItem $item): bool
    {
        $url = $item->displayUrl();

        if ($url === null) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return false;
        }

        return $this->currentRequest()->is(ltrim($path, '/'));
    }

    public function groupUrl(NavigationGroup $group): string
    {
        return $group->items[0]->displayUrl() ?? '#';
    }

    private function renderShellPart(string $part): string
    {
        $panel = $this->currentPanel();

        if ($panel === null) {
            return '';
        }

        $configuredContent = match ($part) {
            'sidebarBrand' => $panel->sidebarBrand,
            'topbarBrand' => $panel->topbarBrand,
            'mobileSidebarBrand' => $panel->mobileSidebarBrand,
            'sidebarFooter' => $panel->sidebarFooter,
            'topbarEnd' => $panel->topbarEnd,
            'mobileHeaderEnd' => $panel->mobileHeaderEnd,
            default => null,
        };

        $content = $configuredContent === null
            ? match ($part) {
                'sidebarBrand' => $this->shell()->sidebarBrand($panel),
                'topbarBrand' => $this->shell()->topbarBrand($panel),
                'mobileSidebarBrand' => $this->shell()->mobileSidebarBrand($panel),
                'sidebarFooter' => $this->shell()->sidebarFooter($panel),
                'topbarEnd' => $this->shell()->topbarEnd($panel),
                'mobileHeaderEnd' => $this->shell()->mobileHeaderEnd($panel),
                default => null,
            }
        : $this->evaluate($configuredContent, [$panel]);

        return $this->renderContent($content);
    }

    private function renderContent(mixed $content): string
    {
        if ($content instanceof View) {
            return $content->render();
        }

        if ($content instanceof Htmlable) {
            return $content->toHtml();
        }

        if ($content === null || is_string($content)) {
            return $content ?? '';
        }

        throw new UnexpectedValueException('Panel shell slots must resolve to views, HTMLable objects, strings, or null.');
    }

    private function currentRequest(): Request
    {
        if ($this->currentPath === '') {
            return app(CurrentRequestResolver::class)->resolve(request());
        }

        return Request::create('/'.ltrim($this->currentPath, '/'));
    }
};
