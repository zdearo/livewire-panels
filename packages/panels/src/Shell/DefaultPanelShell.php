<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Shell;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Stringable;
use Zdearo\LivewirePanels\Panel\Panel;

final class DefaultPanelShell extends PanelShell
{
    #[\Override]
    public function sidebarBrand(Panel $panel): View
    {
        return view('livewire-panels::components.panel-navigation.shell.sidebar-brand', [
            'brandUrl' => $this->brandUrl($panel),
            'panel' => $panel,
        ]);
    }

    #[\Override]
    public function topbarBrand(Panel $panel): View
    {
        return view('livewire-panels::components.panel-navigation.shell.topbar-brand', [
            'brandUrl' => $this->brandUrl($panel),
            'panel' => $panel,
        ]);
    }

    #[\Override]
    public function mobileSidebarBrand(Panel $panel): View
    {
        return $this->sidebarBrand($panel);
    }

    #[\Override]
    public function sidebarFooter(Panel $panel): View|Htmlable|null
    {
        return $this->userMenu($panel, 'sidebar');
    }

    #[\Override]
    public function topbarEnd(Panel $panel): View|Htmlable|null
    {
        return $this->userMenu($panel, 'topbar');
    }

    #[\Override]
    public function mobileHeaderEnd(Panel $panel): View|Htmlable|null
    {
        return $this->userMenu($panel, 'mobile-header');
    }

    private function userMenu(Panel $panel, string $variant): View|Htmlable|null
    {
        if (! $panel->hasAuthentication()) {
            return null;
        }

        $user = auth()->guard($panel->authGuard)->user();

        if (! $user instanceof Authenticatable) {
            return null;
        }

        return view('livewire-panels::components.panel-navigation.shell.user-menu', [
            'panel' => $panel,
            'user' => $user,
            'userName' => $this->userName($user),
            'variant' => $variant,
        ]);
    }

    private function brandUrl(Panel $panel): string
    {
        $navigation = $panel->navigationContract();

        foreach ($navigation->items() as $item) {
            $url = $item->displayUrl();

            if ($url !== null) {
                return $url;
            }
        }

        foreach ($navigation->groups() as $group) {
            foreach ($group->items as $item) {
                $url = $item->displayUrl();

                if ($url !== null) {
                    return $url;
                }
            }
        }

        return url($panel->path);
    }

    private function userName(Authenticatable $user): string
    {
        $name = data_get($user, 'name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $identifier = $user->getAuthIdentifier();

        if (is_scalar($identifier) || $identifier instanceof Stringable) {
            return (string) $identifier;
        }

        return class_basename($user);
    }
}
