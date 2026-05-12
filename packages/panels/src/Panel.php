<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Closure;
use Illuminate\Support\Arr;
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
     * @var array<int, Page>
     */
    public private(set) array $pages = [];

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

    public function page(Page $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * @param  array<int, Page>  $pages
     */
    public function pages(array $pages): self
    {
        foreach ($pages as $page) {
            $this->page($page);
        }

        return $this;
    }

    public function default(bool $condition = true): self
    {
        $this->isDefault = $condition;

        return $this;
    }
}
