<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Illuminate\Routing\Router;
use LogicException;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Page\PageGroup;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Routing\PanelUrlGenerator;
use Zdearo\LivewirePanels\Support\Routing\RouteSegments;

final readonly class NavigationBuilder
{
    public function __construct(
        private PanelUrlGenerator $urls,
        private Router $router,
    ) {}

    public function build(Panel $panel): NavigationContract
    {
        $items = [];
        $groups = array_map(
            fn (NavigationGroup $group): NavigationGroup => clone $group,
            $panel->navigationGroups,
        );

        foreach ($groups as $group) {
            $group->clearItems();
        }

        foreach ($this->resolvedNavigationItems($panel) as $item) {
            if ($item->group === null) {
                $items[] = $item;

                continue;
            }

            if (! isset($groups[$item->group])) {
                throw new LogicException(sprintf(
                    'Navigation group [%s] has not been registered for panel [%s].',
                    $item->group,
                    $panel->id ?? 'unknown',
                ));
            }

            $groups[$item->group]->addItem($item);
        }

        usort(
            $items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        $groups = array_values(array_filter(
            $groups,
            fn (NavigationGroup $group): bool => $group->items !== [] && $group->isVisible(),
        ));

        foreach ($groups as $group) {
            $group->sortItems();
        }

        usort(
            $groups,
            fn (NavigationGroup $first, NavigationGroup $second): int => $first->sort <=> $second->sort,
        );

        return new NavigationContract($items, $groups);
    }

    /**
     * @return array<int, NavigationItem>
     */
    private function resolvedNavigationItems(Panel $panel): array
    {
        $items = $panel->navigation;

        array_push($items, ...$this->resolvedPageNavigationItems($panel, $panel->pages));

        usort(
            $items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        return array_values(array_filter(
            $items,
            fn (NavigationItem $item): bool => $item->isVisible(),
        ));
    }

    /**
     * @param  array<int, Page|PageGroup>  $pages
     * @return array<int, NavigationItem>
     */
    private function resolvedPageNavigationItems(
        Panel $panel,
        array $pages,
        string $pathPrefix = '',
        string $namePrefix = '',
    ): array {
        $items = [];

        foreach ($pages as $page) {
            if ($page instanceof PageGroup) {
                array_push(
                    $items,
                    ...$this->resolvedPageNavigationItems(
                        $panel,
                        $page->pages,
                        RouteSegments::path($pathPrefix, $page->path),
                        RouteSegments::name($namePrefix, $page->name),
                    ),
                );

                continue;
            }

            if ($page->navigation === null) {
                continue;
            }

            $item = clone $page->navigation;
            $pageUrl = $this->pageUrl($panel, $page, $pathPrefix, $namePrefix);

            if ($item->url === null) {
                $item->url($pageUrl);
            }

            if ($item->activeUrl === null) {
                $item->activeUrl($pageUrl);
            }

            $items[] = $item;
        }

        return $items;
    }

    private function pageUrl(Panel $panel, Page $page, string $pathPrefix = '', string $namePrefix = ''): string
    {
        $routeName = RouteSegments::name($namePrefix, $page->name);

        if ($routeName !== '' && $this->router->has($panel->id.'.'.$routeName)) {
            return $this->urls->route($panel, $routeName, absolute: false);
        }

        return RouteSegments::path($panel->path, $pathPrefix, $page->path);
    }
}
