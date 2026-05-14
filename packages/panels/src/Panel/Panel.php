<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use UnexpectedValueException;
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
use Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures;
use Zdearo\LivewirePanels\Tenant\Tenant;

final class Panel
{
    use ConfiguresPropertiesOnce;
    use EvaluatesClosures;

    public private(set) string $id;

    public private(set) string $path;

    public private(set) ?string $subdomain = null;

    /**
     * @var string|Closure(): string
     */
    public private(set) string|Closure $name;

    public private(set) string $appLayout = 'livewire-panels::layouts.app';

    public private(set) string $layout = 'livewire-panels::layouts.panel';

    public private(set) bool $isDefault = false;

    /**
     * @var NavigationMode|Closure(): (NavigationMode|string)
     */
    public private(set) NavigationMode|Closure $navigationMode = NavigationMode::Sidebar;

    /**
     * @var class-string<PanelShell>
     */
    public private(set) string $shell = DefaultPanelShell::class;

    public private(set) ?string $authGuard = null;

    public private(set) ?string $loginRoute = null;

    public private(set) ?string $logoutRoute = null;

    public private(set) ?Tenant $tenant = null;

    public private(set) bool $requiresTenant = false;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $sidebarBrand = null;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $topbarBrand = null;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $mobileSidebarBrand = null;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $sidebarFooter = null;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $topbarEnd = null;

    /**
     * @var View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null
     */
    public private(set) View|Htmlable|string|Closure|null $mobileHeaderEnd = null;

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
        return new self;
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

    public function subdomain(?string $subdomain): self
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    /**
     * @param  string|Closure(): string  $name
     */
    public function name(string|Closure $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function displayName(): string
    {
        $name = $this->evaluate($this->name);

        if (! is_string($name)) {
            throw new UnexpectedValueException('Panel names must resolve to strings.');
        }

        $translation = __($name);

        return is_string($translation) ? $translation : $name;
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

    /**
     * @param  NavigationMode|string|Closure(): (NavigationMode|string)  $mode
     */
    public function navigationMode(NavigationMode|string|Closure $mode): self
    {
        $this->navigationMode = is_string($mode) ? NavigationMode::from($mode) : $mode;

        return $this;
    }

    public function displayNavigationMode(): NavigationMode
    {
        $mode = $this->evaluate($this->navigationMode);

        if (is_string($mode)) {
            return NavigationMode::from($mode);
        }

        if (! $mode instanceof NavigationMode) {
            throw new UnexpectedValueException('Panel navigation modes must resolve to NavigationMode instances or strings.');
        }

        return $mode;
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

    public function logoutRoute(?string $route): self
    {
        $this->logoutRoute = $route;

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

    public function tenant(Tenant $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function requiresTenant(bool $condition = true): self
    {
        $this->requiresTenant = $condition;

        return $this;
    }

    public function hasTenancy(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function sidebarBrand(View|Htmlable|string|Closure|null $content): self
    {
        $this->sidebarBrand = $content;

        return $this;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function topbarBrand(View|Htmlable|string|Closure|null $content): self
    {
        $this->topbarBrand = $content;

        return $this;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function mobileSidebarBrand(View|Htmlable|string|Closure|null $content): self
    {
        $this->mobileSidebarBrand = $content;

        return $this;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function sidebarFooter(View|Htmlable|string|Closure|null $content): self
    {
        $this->sidebarFooter = $content;

        return $this;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function topbarEnd(View|Htmlable|string|Closure|null $content): self
    {
        $this->topbarEnd = $content;

        return $this;
    }

    /**
     * @param  View|Htmlable|string|Closure(self): (View|Htmlable|string|null)|null  $content
     */
    public function mobileHeaderEnd(View|Htmlable|string|Closure|null $content): self
    {
        $this->mobileHeaderEnd = $content;

        return $this;
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
