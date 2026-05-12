<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

final readonly class NavigationContract
{
    /**
     * @param  array<int, NavigationItem>  $items
     * @param  array<int, NavigationGroup>  $groups
     */
    public function __construct(
        private array $items,
        private array $groups,
    ) {}

    /**
     * @return array<int, NavigationItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return array<int, NavigationGroup>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function allItems(): array
    {
        $items = $this->items;

        foreach ($this->groups as $group) {
            array_push($items, ...$group->items);
        }

        usort(
            $items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        return $items;
    }
}
