<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Panel\Panel;

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

    public function currentItemFor(Request $request, ?Panel $panel = null): ?NavigationItem
    {
        $currentItem = null;
        $currentSpecificity = -1;

        foreach ($this->allItems() as $item) {
            if (! $item->isCurrentFor($request, $panel)) {
                continue;
            }

            $specificity = $this->pathSpecificity($item);

            if ($specificity > $currentSpecificity) {
                $currentItem = $item;
                $currentSpecificity = $specificity;
            }
        }

        return $currentItem;
    }

    public function itemIsCurrentFor(NavigationItem $item, Request $request, ?Panel $panel = null): bool
    {
        return $this->currentItemFor($request, $panel) === $item;
    }

    private function pathSpecificity(NavigationItem $item): int
    {
        $url = $item->displayUrl();

        if ($url === null) {
            return -1;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path)) {
            return -1;
        }

        $path = trim($path, '/');

        if ($path === '') {
            return 0;
        }

        return count(explode('/', $path));
    }
}
