<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Routing;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRouting\LivewirePageController;
use Zdearo\LivewirePanels\Middleware\AuthenticatePanel;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Middleware\SetCurrentTenant;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Page\PageGroup;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Routing\RouteSegments;

final class PanelRouter
{
    public function register(Panel $panel): void
    {
        $middleware = array_values(array_unique([
            'web',
            SetCurrentPanel::class.':'.$panel->id,
            SetCurrentTenant::class.':'.$panel->id,
            ...($panel->hasAuthentication() ? [AuthenticatePanel::class.':'.$panel->id] : []),
            ...$panel->middleware,
        ]));

        $this->routeRegistrar($panel, $middleware)
            ->group(function () use ($panel): void {
                $this->registerPages($panel->pages);
            });

        foreach ($panel->routes as $routes) {
            $this->routeRegistrar($panel, $middleware)
                ->group($routes);
        }

        Route::getRoutes()->refreshNameLookups();
    }

    /**
     * @param  array<int, string>  $middleware
     */
    private function routeRegistrar(Panel $panel, array $middleware): RouteRegistrar
    {
        $registrar = Route::middleware($middleware)
            ->withoutMiddleware($panel->withoutMiddleware)
            ->prefix($panel->path)
            ->as($panel->id.'.');

        $domain = $this->domain($panel);

        if ($domain !== null) {
            $registrar->domain($domain);
        }

        return $registrar;
    }

    private function domain(Panel $panel): ?string
    {
        if ($panel->subdomain === null) {
            return null;
        }

        $baseDomain = $this->baseDomain();

        if ($baseDomain === null) {
            return $panel->subdomain;
        }

        return trim($panel->subdomain, '.').'.'.$baseDomain;
    }

    private function baseDomain(): ?string
    {
        $appUrl = config('app.url');

        if (! is_string($appUrl) || $appUrl === '') {
            return null;
        }

        $host = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            $host = parse_url('https://'.$appUrl, PHP_URL_HOST);
        }

        if (! is_string($host) || $host === '') {
            return null;
        }

        return $host;
    }

    /**
     * @param  array<int, Page|PageGroup>  $pages
     */
    private function registerPages(array $pages, string $pathPrefix = '', string $namePrefix = ''): void
    {
        foreach ($pages as $page) {
            if ($page instanceof PageGroup) {
                $this->registerPages(
                    $page->pages,
                    RouteSegments::path($pathPrefix, $page->path),
                    RouteSegments::name($namePrefix, $page->name),
                );

                continue;
            }

            $route = $this->livewireRoute($page, RouteSegments::path($pathPrefix, $page->path));

            if ($page->name !== null) {
                $route->name(RouteSegments::name($namePrefix, $page->name));
            }
        }
    }

    private function livewireRoute(Page $page, string $path): LaravelRoute
    {
        $route = Route::get($path, LivewirePageController::class);
        $route->action['livewire_component'] = $page->component;

        return $route;
    }
}
