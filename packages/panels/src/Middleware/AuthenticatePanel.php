<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

final readonly class AuthenticatePanel
{
    public function __construct(
        private PanelManager $manager,
        private AuthFactory $auth,
    ) {}

    /**
     * @param  Closure(Request): mixed  $next
     *
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next, string $panel): mixed
    {
        $currentPanel = $this->manager->panel($panel);

        if (! $currentPanel->hasAuthentication()) {
            return $next($request);
        }

        $user = $this->auth->guard($currentPanel->authGuard)->user();

        if ($user === null) {
            throw new AuthenticationException;
        }

        if (! $this->matchesAllowedAuthenticatable($currentPanel, $user)) {
            throw new AuthorizationException;
        }

        if ($user instanceof CanAccessPanel && ! $user->canAccessPanel($currentPanel)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }

    private function matchesAllowedAuthenticatable(Panel $panel, object $user): bool
    {
        return array_any($panel->authenticatables, fn ($authenticatable): bool => $user instanceof $authenticatable);
    }
}
