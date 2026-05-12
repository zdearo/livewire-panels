<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Middleware;

use Closure;
use Illuminate\Http\Request;
use Zdearo\LivewirePanels\PanelManager;

final readonly class SetCurrentPanel
{
    public function __construct(
        private PanelManager $manager,
    ) {}

    /**
     * @param  Closure(Request): mixed  $next
     */
    public function handle(Request $request, Closure $next, string $panel): mixed
    {
        $currentPanel = $this->manager->panel($panel);

        $this->manager->setCurrentPanel($currentPanel);

        config(['livewire.component_layout' => $currentPanel->layout]);

        return $next($request);
    }
}
