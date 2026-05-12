<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Support\Facades\Route;

final class PanelRouter
{
    public function register(Panel $panel): void
    {
        foreach ($panel->routes as $routes) {
            Route::middleware($panel->middleware)
                ->withoutMiddleware($panel->withoutMiddleware)
                ->prefix($panel->path)
                ->as($panel->id.'.')
                ->group($routes);
        }

        Route::getRoutes()->refreshNameLookups();
    }
}
