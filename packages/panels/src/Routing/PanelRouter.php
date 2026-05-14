<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Routing;

use Illuminate\Routing\Route as LaravelRoute;
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
        $middleware = [
            ...$panel->middleware,
            SetCurrentPanel::class.':'.$panel->id,
            SetCurrentTenant::class.':'.$panel->id,
        ];

        if ($panel->hasAuthentication()) {
            $middleware[] = AuthenticatePanel::class.':'.$panel->id;
        }

        Route::middleware($middleware)
            ->withoutMiddleware($panel->withoutMiddleware)
            ->prefix($panel->path)
            ->as($panel->id.'.')
            ->group(function () use ($panel): void {
                $this->registerPages($panel->pages);
            });

        foreach ($panel->routes as $routes) {
            Route::middleware($middleware)
                ->withoutMiddleware($panel->withoutMiddleware)
                ->prefix($panel->path)
                ->as($panel->id.'.')
                ->group($routes);
        }

        Route::getRoutes()->refreshNameLookups();
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
