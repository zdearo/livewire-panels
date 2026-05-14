<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zdearo\LivewirePanels\Facades\LivewirePanels;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Middleware\SetCurrentTenant;
use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelProvider;
use Zdearo\LivewirePanels\Panel\PanelRegistry;
use Zdearo\LivewirePanels\Routing\PanelUrlGenerator;
use Zdearo\LivewirePanels\Tenant\Contracts\HasPanelTenants;
use Zdearo\LivewirePanels\Tenant\Contracts\ResolvesPanelTenant;
use Zdearo\LivewirePanels\Tenant\Tenant;
use Zdearo\LivewirePanels\Tenant\TenantManager;
use Zdearo\LivewirePanels\Tenant\TenantResolver;

it('configures tenant resolution on a panel', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin/{company}')
        ->name('Admin')
        ->tenant(
            Tenant::make(PanelTenant::class)
                ->routeParameter('company')
                ->resolver(PanelTenantResolver::class),
        )
        ->requiresTenant();

    expect($panel->hasTenancy())->toBeTrue()
        ->and($panel->tenant)->toBeInstanceOf(Tenant::class)
        ->and($panel->tenant?->model)->toBe(PanelTenant::class)
        ->and($panel->tenant?->routeParameter)->toBe('company')
        ->and($panel->tenant?->resolver)->toBe(PanelTenantResolver::class)
        ->and($panel->requiresTenant)->toBeTrue();
});

it('adds tenant middleware to panel routes', function (): void {
    app()->register(TenantTestingPanelProvider::class);
    app()->boot();

    $route = Route::getRoutes()->getByName('admin.dashboard');

    expect($route)
        ->not->toBeNull()
        ->gatherMiddleware()->toContain(
            SetCurrentPanel::class.':admin',
            SetCurrentTenant::class.':admin',
        );
});

it('resolves the current tenant from the configured route parameter', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin/{company}')
        ->name('Admin')
        ->tenant(Tenant::make(PanelTenant::class)->routeParameter('company'));

    app(PanelRegistry::class)->register($panel);

    $request = tenantRequest('/admin/acme', '/admin/{company}');

    $response = app(SetCurrentTenant::class)->handle(
        $request,
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect($response->getContent())->toBe('ok')
        ->and(LivewirePanels::currentTenant())->toBeInstanceOf(PanelTenant::class)
        ->and(LivewirePanels::currentTenant()?->getRouteKey())->toBe('acme');
});

it('uses a custom tenant resolver when one is configured', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->tenant(Tenant::make(PanelTenant::class)->resolver(PanelTenantResolver::class));

    app(PanelRegistry::class)->register($panel);

    app(SetCurrentTenant::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect(LivewirePanels::currentTenant())
        ->toBeInstanceOf(PanelTenant::class)
        ->getRouteKey()->toBe('resolved');
});

it('clears the current tenant when the panel has no tenancy configured', function (): void {
    LivewirePanels::setCurrentTenant(new PanelTenant('old'));

    $panel = Panel::make()
        ->id('public')
        ->path('public')
        ->name('Public');

    app(PanelRegistry::class)->register($panel);

    app(SetCurrentTenant::class)->handle(
        Request::create('/public'),
        fn (): Response => new Response('ok'),
        'public',
    );

    expect(LivewirePanels::currentTenant())->toBeNull();
});

it('fails when a required tenant cannot be resolved', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin/{company}')
        ->name('Admin')
        ->tenant(Tenant::make(PanelTenant::class)->routeParameter('company'))
        ->requiresTenant();

    app(PanelRegistry::class)->register($panel);

    expect(fn () => app(SetCurrentTenant::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(NotFoundHttpException::class, 'Panel [admin] requires a tenant.');
});

it('allows missing tenants when tenancy is optional', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->tenant(Tenant::make(PanelTenant::class)->routeParameter('company'));

    app(PanelRegistry::class)->register($panel);

    $response = app(SetCurrentTenant::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect($response->getContent())->toBe('ok')
        ->and(LivewirePanels::currentTenant())->toBeNull();
});

it('denies authenticated users that cannot access the resolved tenant', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin/{company}')
        ->name('Admin')
        ->authenticatables(PanelTenantUser::class)
        ->tenant(Tenant::make(PanelTenant::class)->routeParameter('company'));

    app(PanelRegistry::class)->register($panel);
    $this->be(new PanelTenantUser(canAccessTenants: false));

    expect(fn () => app(SetCurrentTenant::class)->handle(
        tenantRequest('/admin/acme', '/admin/{company}'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(AuthorizationException::class);
});

it('generates panel routes with current tenant parameters', function (): void {
    app()->register(TenantTestingPanelProvider::class);
    app()->boot();

    LivewirePanels::setCurrentTenant(new PanelTenant('acme'));

    expect(LivewirePanels::tenantRouteParameters())->toBe(['company' => 'acme'])
        ->and(LivewirePanels::route('users'))->toBe('http://localhost/admin/acme/users')
        ->and(LivewirePanels::route('users.show', ['user' => 10]))->toBe('http://localhost/admin/acme/users/10');
});

it('generates tenant-aware urls outside the panel manager', function (): void {
    app()->register(TenantTestingPanelProvider::class);
    app()->boot();

    $panel = LivewirePanels::panel('admin');
    LivewirePanels::setCurrentTenant(new PanelTenant('acme'));

    expect(app(PanelUrlGenerator::class)->tenantRouteParameters($panel))->toBe(['company' => 'acme'])
        ->and(app(PanelUrlGenerator::class)->route($panel, 'users'))->toBe('http://localhost/admin/acme/users')
        ->and(app(PanelUrlGenerator::class)->route($panel, 'admin.users.show', ['user' => 10]))
        ->toBe('http://localhost/admin/acme/users/10');
});

it('returns no tenant route parameters when no tenant can be applied', function (): void {
    $panel = Panel::make()
        ->id('public')
        ->path('public')
        ->name('Public');

    expect(app(TenantManager::class)->routeParameters($panel))->toBe([]);
});

it('can use a plain tenant object as a route parameter value', function (): void {
    $tenant = new PlainPanelTenant;
    $panel = Panel::make()
        ->id('admin')
        ->path('admin/{company}')
        ->name('Admin')
        ->tenant(Tenant::make(PlainPanelTenant::class)->routeParameter('company'));

    expect(app(TenantManager::class)->routeParameters($panel, $tenant))->toBe(['company' => $tenant]);
});

it('returns null when the default resolver has no route parameter configured', function (): void {
    $tenant = app(TenantResolver::class)->resolve(
        Panel::make()->id('admin')->path('admin')->name('Admin'),
        Tenant::make(PanelTenant::class),
        Request::create('/admin'),
    );

    expect($tenant)->toBeNull();
});

it('returns an already-bound tenant route parameter', function (): void {
    $request = Request::create('/admin/acme');
    $request->setRouteResolver(fn () => new BoundTenantRoute(new PanelTenant('bound')));

    $tenant = app(TenantResolver::class)->resolve(
        Panel::make()->id('admin')->path('admin/{company}')->name('Admin'),
        Tenant::make(PanelTenant::class)->routeParameter('company'),
        $request,
    );

    expect($tenant)
        ->toBeInstanceOf(PanelTenant::class)
        ->getRouteKey()->toBe('bound');
});

it('returns null when a scalar tenant route parameter cannot be resolved by the model', function (): void {
    $tenant = app(TenantResolver::class)->resolve(
        Panel::make()->id('admin')->path('admin/{company}')->name('Admin'),
        Tenant::make(PlainPanelTenant::class)->routeParameter('company'),
        tenantRequest('/admin/acme', '/admin/{company}'),
    );

    expect($tenant)->toBeNull();
});

it('resolves tenants from the original request during Livewire updates', function (): void {
    app()->instance('originalRequest', tenantRequest('/admin/acme', '/admin/{company}'));

    $tenant = app(TenantResolver::class)->resolve(
        Panel::make()->id('admin')->path('admin/{company}')->name('Admin'),
        Tenant::make(PanelTenant::class)->routeParameter('company'),
        Request::create('/livewire/update'),
    );

    expect($tenant)
        ->toBeInstanceOf(PanelTenant::class)
        ->getRouteKey()->toBe('acme');
});

it('builds page navigation urls from named routes with current tenant parameters', function (): void {
    app()->register(TenantTestingPanelProvider::class);
    app()->boot();

    LivewirePanels::setCurrentTenant(new PanelTenant('acme'));

    $navigation = LivewirePanels::panel('admin')->navigationContract();

    expect($navigation->items())
        ->toHaveCount(2)
        ->sequence(
            fn ($item) => $item->label->toBe('Dashboard')->url->toBe('/admin/acme'),
            fn ($item) => $item->label->toBe('Users')->url->toBe('/admin/acme/users'),
        );
});

function tenantRequest(string $uri, string $routeUri): Request
{
    $request = Request::create($uri);
    $route = Route::get($routeUri, fn (): string => 'ok');
    $route->bind($request);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

final class PanelTenant implements UrlRoutable
{
    public function __construct(
        public string $id = 'acme',
    ) {}

    public function getRouteKey(): string
    {
        return $this->id;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null): self
    {
        return new self((string) $value);
    }

    public function resolveChildRouteBinding($childType, $value, $field): never
    {
        throw new RuntimeException('Child route binding is not supported by this test tenant.');
    }
}

final class PanelTenantResolver implements ResolvesPanelTenant
{
    public function resolve(Panel $panel, Tenant $tenant, Request $request): ?object
    {
        return new PanelTenant('resolved');
    }
}

final class PlainPanelTenant {}

final readonly class BoundTenantRoute
{
    public function __construct(
        private object $tenant,
    ) {}

    public function parameter(string $name, mixed $default = null): object
    {
        return $this->tenant;
    }
}

final class PanelTenantUser extends Authenticatable implements HasPanelTenants
{
    public function __construct(
        private readonly bool $canAccessTenants = true,
    ) {
        parent::__construct();
    }

    public function panelTenants(Panel $panel): iterable
    {
        return [new PanelTenant('acme')];
    }

    public function canAccessPanelTenant(Panel $panel, object $tenant): bool
    {
        return $this->canAccessTenants;
    }
}

final class TenantTestingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin/{company}')
            ->name('Admin')
            ->tenant(Tenant::make(PanelTenant::class)->routeParameter('company'))
            ->pages([
                Page::make('/', 'pages::admin.dashboard')
                    ->name('dashboard')
                    ->navigation('Dashboard', icon: 'home'),
                Page::make('/users', 'pages::admin.users')
                    ->name('users')
                    ->navigation('Users', icon: 'users'),
                Page::make('/users/{user}', 'pages::admin.users.show')
                    ->name('users.show'),
            ])
            ->default();
    }
}
