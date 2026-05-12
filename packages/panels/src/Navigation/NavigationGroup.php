<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Illuminate\Support\Str;

final class NavigationGroup
{
    public private(set) string $id;

    public private(set) string $label;

    public private(set) ?string $icon = null;

    public private(set) int $sort = 0;

    /**
     * @var array<int, NavigationItem>
     */
    public private(set) array $items = [];

    public static function make(string $id): self
    {
        $group = new self;
        $group->id = $id;
        $group->label = (string) Str::of($id)->replace(['-', '_'], ' ')->headline();

        return $group;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function sort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function addItem(NavigationItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function clearItems(): self
    {
        $this->items = [];

        return $this;
    }

    public function sortItems(): self
    {
        usort(
            $this->items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        return $this;
    }
}
