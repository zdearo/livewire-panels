<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Icons;

use Illuminate\Contracts\Support\Htmlable;
use InvalidArgumentException;

final class PanelsIconManager
{
    /**
     * @var array<string, string|Htmlable>
     */
    private array $icons;

    public function __construct()
    {
        $this->icons = $this->defaultIcons();
    }

    /**
     * @param  array<string, string|Htmlable>  $icons
     */
    public function register(array $icons): void
    {
        $this->icons = [
            ...$this->icons,
            ...$icons,
        ];
    }

    public function resolve(string $alias): string|Htmlable
    {
        return $this->icons[$alias]
            ?? throw new InvalidArgumentException("No Livewire Panels icon has been registered for alias [{$alias}].");
    }

    /**
     * @return array<string, string|Htmlable>
     */
    private function defaultIcons(): array
    {
        return [
            PanelsIconAlias::SIDEBAR_TOGGLE_BUTTON => 'heroicon-o-bars-2',
            PanelsIconAlias::TOPBAR_GROUP_DROPDOWN_BUTTON => 'heroicon-o-chevron-down',
            PanelsIconAlias::USER_MENU_LOGOUT_BUTTON => 'heroicon-o-arrow-right-start-on-rectangle',
        ];
    }
}
