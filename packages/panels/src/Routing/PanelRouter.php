<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Routing;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRouting\LivewirePageController;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\Panel;

final class PanelRouter
{
    public function register(Panel $panel): void
    {
        $middleware = [
            ...$panel->middleware,
            SetCurrentPanel::class.':'.$panel->id,
        ];

        Route::middleware($middleware)
            ->withoutMiddleware($panel->withoutMiddleware)
            ->prefix($panel->path)
            ->as($panel->id.'.')
            ->group(function () use ($panel): void {
                foreach ($panel->pages as $page) {
                    $route = $this->livewireRoute($page);

                    if ($page->name !== null) {
                        $route->name($page->name);
                    }
                }
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

    private function livewireRoute(Page $page): LaravelRoute
    {
        $route = Route::get($page->path, LivewirePageController::class);
        $route->action['livewire_component'] = $page->component;

        return $route;
    }
}
