<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

final class Page
{
    public private(set) string $path;

    public private(set) string $component;

    public private(set) ?string $name = null;

    public private(set) ?NavigationItem $navigation = null;

    public static function make(string $path, string $component): self
    {
        $page = new self;
        $page->path = $path;
        $page->component = $component;

        return $page;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function navigation(
        string $label,
        ?string $icon = null,
        ?string $group = null,
        int $sort = 0,
    ): self {
        $this->navigation = NavigationItem::make($label)
            ->icon($icon)
            ->group($group)
            ->sort($sort);

        return $this;
    }
}
