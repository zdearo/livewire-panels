<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Closure;
use Illuminate\Support\Arr;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Support\Concerns\ConfiguresPropertiesOnce;

final class Panel
{
    use ConfiguresPropertiesOnce;

    public private(set) string $id;

    public private(set) string $path;

    public private(set) string $name;

    public private(set) string $appLayout = 'livewire-panels::layouts.app';

    public private(set) string $layout = 'livewire-panels::layouts.panel';

    public private(set) bool $isDefault = false;

    public private(set) ?string $authGuard = null;

    /**
     * @var array<int, class-string>
     */
    public private(set) array $authenticatables = [];

    /**
     * @var array<int, string>
     */
    public private(set) array $vite = [];

    /**
     * @var array<int, string>
     */
    public private(set) array $middleware = [];

    /**
     * @var array<int, string>
     */
    public private(set) array $withoutMiddleware = [];

    /**
     * @var array<int, Closure(): void>
     */
    public private(set) array $routes = [];

    /**
     * @var array<int, Page|PageGroup>
     */
    public private(set) array $pages = [];

    /**
     * @var array<int, NavigationItem>
     */
    public private(set) array $navigation = [];

    /**
     * @var array<string, NavigationGroup>
     */
    public private(set) array $navigationGroups = [];

    public static function make(): self
    {
        return app(self::class);
    }

    public function id(string $id): self
    {
        $this->guardAgainstConfiguringPropertyAgain(
            isset($this->id),
            $this->id ?? '',
            'panel',
            'ID',
        );

        $this->id = $id;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function appLayout(string $layout): self
    {
        $this->appLayout = $layout;

        return $this;
    }

    public function authGuard(?string $guard): self
    {
        $this->authGuard = $guard;

        return $this;
    }

    /**
     * @param  array<int, class-string>|class-string  $models
     */
    public function authenticatables(array|string $models): self
    {
        $this->authenticatables = Arr::wrap($models);

        return $this;
    }

    public function hasAuthentication(): bool
    {
        return $this->authenticatables !== [];
    }

    /**
     * @param  array<int, string>|string  $entries
     */
    public function vite(array|string $entries): self
    {
        $this->vite = Arr::wrap($entries);

        return $this;
    }

    /**
     * @param  array<int, string>|string  $middleware
     */
    public function middleware(array|string $middleware): self
    {
        $this->middleware = Arr::wrap($middleware);

        return $this;
    }

    /**
     * @param  array<int, string>|string  $middleware
     */
    public function withoutMiddleware(array|string $middleware): self
    {
        $this->withoutMiddleware = Arr::wrap($middleware);

        return $this;
    }

    /**
     * @param  Closure(): void  $routes
     */
    public function routes(Closure $routes): self
    {
        $this->routes[] = $routes;

        return $this;
    }

    public function page(Page|PageGroup $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * @param  array<int, Page|PageGroup>  $pages
     */
    public function pages(array $pages): self
    {
        foreach ($pages as $page) {
            $this->page($page);
        }

        return $this;
    }

    /**
     * @param  array<int, NavigationItem>|NavigationItem  $items
     */
    public function navigation(array|NavigationItem $items): self
    {
        foreach (Arr::wrap($items) as $item) {
            $this->navigation[] = $item;
        }

        return $this;
    }

    /**
     * @param  array<int, NavigationGroup>|NavigationGroup  $groups
     */
    public function navigationGroups(array|NavigationGroup $groups): self
    {
        foreach (Arr::wrap($groups) as $group) {
            $this->navigationGroups[$group->id] = $group;
        }

        return $this;
    }

    public function navigationContract(): NavigationContract
    {
        $items = [];
        $groups = array_map(
            fn (NavigationGroup $group): NavigationGroup => clone $group,
            $this->navigationGroups,
        );

        foreach ($groups as $group) {
            $group->clearItems();
        }

        foreach ($this->resolvedNavigationItems() as $item) {
            if ($item->group === null) {
                $items[] = $item;

                continue;
            }

            if (! isset($groups[$item->group])) {
                throw new \LogicException(sprintf(
                    'Navigation group [%s] has not been registered for panel [%s].',
                    $item->group,
                    $this->id ?? 'unknown',
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
            fn (NavigationGroup $group): bool => $group->items !== [],
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
    public function navigationItems(): array
    {
        return $this->navigationContract()->allItems();
    }

    /**
     * @return array<int, NavigationItem>
     */
    private function resolvedNavigationItems(): array
    {
        $items = $this->navigation;

        array_push($items, ...$this->resolvedPageNavigationItems($this->pages));

        usort(
            $items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        return $items;
    }

    /**
     * @param  array<int, Page|PageGroup>  $pages
     * @return array<int, NavigationItem>
     */
    private function resolvedPageNavigationItems(array $pages, string $pathPrefix = ''): array
    {
        $items = [];

        foreach ($pages as $page) {
            if ($page instanceof PageGroup) {
                array_push(
                    $items,
                    ...$this->resolvedPageNavigationItems(
                        $page->pages,
                        $this->joinPaths($pathPrefix, $page->path),
                    ),
                );

                continue;
            }

            if ($page->navigation === null) {
                continue;
            }

            $item = clone $page->navigation;

            if ($item->url === null) {
                $item->url($this->pageUrl($page, $pathPrefix));
            }

            $items[] = $item;
        }

        return $items;
    }

    private function pageUrl(Page $page, string $pathPrefix = ''): string
    {
        return $this->joinPaths($this->path, $pathPrefix, $page->path);
    }

    private function joinPaths(string ...$paths): string
    {
        $segments = [];

        foreach ($paths as $path) {
            $path = trim($path, '/');

            if ($path !== '') {
                $segments[] = $path;
            }
        }

        $path = implode('/', $segments);

        return $path === '' ? '/' : "/{$path}";
    }

    public function default(bool $condition = true): self
    {
        $this->isDefault = $condition;

        return $this;
    }
}
