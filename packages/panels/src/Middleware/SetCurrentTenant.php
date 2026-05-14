<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;
use Zdearo\LivewirePanels\Tenant\Contracts\HasPanelTenants;
use Zdearo\LivewirePanels\Tenant\Tenant;
use Zdearo\LivewirePanels\Tenant\TenantManager;
use Zdearo\LivewirePanels\Tenant\TenantResolver;

final readonly class SetCurrentTenant
{
    public function __construct(
        private PanelManager $panels,
        private TenantManager $tenants,
        private TenantResolver $resolver,
        private AuthFactory $auth,
    ) {}

    /**
     * @param  Closure(Request): mixed  $next
     *
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next, string $panel): mixed
    {
        $currentPanel = $this->panels->panel($panel);

        $tenantConfiguration = $currentPanel->tenant;

        if ($tenantConfiguration === null) {
            $this->tenants->setCurrentTenant(null);

            return $next($request);
        }

        $tenant = $this->resolveTenant($currentPanel, $tenantConfiguration, $request);

        if ($tenant === null && $currentPanel->requiresTenant) {
            throw new NotFoundHttpException(sprintf('Panel [%s] requires a tenant.', $currentPanel->id));
        }

        $this->authorizeTenantAccess($currentPanel, $tenant);
        $this->tenants->setCurrentTenant($tenant);

        return $next($request);
    }

    private function resolveTenant(Panel $panel, Tenant $tenant, Request $request): ?object
    {
        if ($tenant->resolver !== null) {
            return app($tenant->resolver)->resolve($panel, $tenant, $request);
        }

        return $this->resolver->resolve($panel, $tenant, $request);
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizeTenantAccess(Panel $panel, ?object $tenant): void
    {
        if ($tenant === null) {
            return;
        }

        $user = $this->auth->guard($panel->authGuard)->user();

        if (! $user instanceof HasPanelTenants) {
            return;
        }

        if (! $user->canAccessPanelTenant($panel, $tenant)) {
            throw new AuthorizationException;
        }
    }
}
