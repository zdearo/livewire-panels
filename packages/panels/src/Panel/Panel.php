<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Closure;
use Illuminate\Support\Arr;
use Zdearo\LivewirePanels\Enums\NavigationMode;
use Zdearo\LivewirePanels\Navigation\NavigationBuilder;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Page\PageGroup;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;
use Zdearo\LivewirePanels\Shell\PanelShell;
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

    public private(set) NavigationMode $navigationMode = NavigationMode::Sidebar;

    /**
     * @var class-string<PanelShell>
     */
    public private(set) string $shell = DefaultPanelShell::class;

    public private(set) ?string $authGuard = null;

    public private(set) ?string $loginRoute = null;

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

    public function navigationMode(NavigationMode|string $mode): self
    {
        $this->navigationMode = is_string($mode) ? NavigationMode::from($mode) : $mode;

        return $this;
    }

    /**
     * @param  class-string<PanelShell>  $shell
     */
    public function shell(string $shell): self
    {
        $this->shell = $shell;

        return $this;
    }

    public function authGuard(?string $guard): self
    {
        $this->authGuard = $guard;

        return $this;
    }

    public function loginRoute(?string $route): self
    {
        $this->loginRoute = $route;

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
        return app(NavigationBuilder::class)->build($this);
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function navigationItems(): array
    {
        return $this->navigationContract()->allItems();
    }

    public function default(bool $condition = true): self
    {
        $this->isDefault = $condition;

        return $this;
    }
}
