<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Page;

use Closure;
use Zdearo\LivewirePanels\Navigation\NavigationItem;

final class Page
{
    public private(set) string $path;

    public private(set) string $component;

    public private(set) ?string $name = null;

    public private(set) ?NavigationItem $navigation = null;

    public static function make(string $path, string $component): static
    {
        $page = new self;
        $page->path = $path;
        $page->component = $component;

        return $page;
    }

    public static function group(string $path): PageGroup
    {
        return PageGroup::make($path);
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function navigation(
        string|Closure $label,
        ?string $icon = null,
        ?string $group = null,
        int $sort = 0,
        ?bool $spa = null,
    ): static {
        $this->navigation = NavigationItem::make($label)
            ->icon($icon)
            ->group($group)
            ->sort($sort);

        if ($spa !== null) {
            $this->navigation->spa($spa);
        }

        return $this;
    }
}
