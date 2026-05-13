<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Zdearo\LivewirePanels\Auth\Contracts\CanAccessPanel;
use Zdearo\LivewirePanels\Middleware\AuthenticatePanel;
use Zdearo\LivewirePanels\Middleware\SetCurrentPanel;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelProvider;
use Zdearo\LivewirePanels\Panel\PanelRegistry;

beforeEach(function (): void {
    config([
        'auth.defaults.guard' => 'web',
        'auth.guards.web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'auth.guards.admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'auth.providers.users' => [
            'driver' => 'eloquent',
            'model' => PanelUser::class,
        ],
        'auth.providers.admins' => [
            'driver' => 'eloquent',
            'model' => PanelAdmin::class,
        ],
    ]);
});

it('does not authenticate a panel without authenticatable models', function (): void {
    app()->register(PublicPanelProvider::class);
    app()->boot();

    $route = Route::getRoutes()->getByName('public.home');

    expect($route)
        ->not->toBeNull()
        ->gatherMiddleware()->toContain(SetCurrentPanel::class.':public')
        ->not->toContain(AuthenticatePanel::class.':public');
});

it('adds panel authentication middleware when authenticatable models are configured', function (): void {
    app()->register(AuthenticatedPanelProvider::class);
    app()->boot();

    $route = Route::getRoutes()->getByName('admin.home');

    expect($route)
        ->not->toBeNull()
        ->gatherMiddleware()->toContain(
            SetCurrentPanel::class.':admin',
            AuthenticatePanel::class.':admin',
        );
});

it('allows requests through the authentication middleware when the panel has no authenticatables', function (): void {
    $panel = Panel::make()
        ->id('public')
        ->path('public')
        ->name('Public');

    app(PanelRegistry::class)->register($panel);

    $response = app(AuthenticatePanel::class)->handle(
        Request::create('/public'),
        fn (): Response => new Response('ok'),
        'public',
    );

    expect($response->getContent())->toBe('ok');
});

it('requires an authenticated user when authenticatable models are configured', function (): void {
    Route::get('/admin/login', fn (): string => 'Login')->name('admin.login');

    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelUser::class);

    app(PanelRegistry::class)->register($panel);

    expect(fn () => app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(AuthenticationException::class);
});

it('redirects unauthenticated panel requests to the conventional panel login route', function (): void {
    Route::get('/admin/login', fn (): string => 'Login')->name('admin.login');

    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelUser::class);

    app(PanelRegistry::class)->register($panel);

    try {
        app(AuthenticatePanel::class)->handle(
            Request::create('/admin'),
            fn (): Response => new Response('ok'),
            'admin',
        );
    } catch (AuthenticationException $exception) {
        expect($exception->redirectTo(Request::create('/admin')))->toBe('http://localhost/admin/login');

        return;
    }

    $this->fail('Expected an authentication exception.');
});

it('redirects unauthenticated panel requests to a configured login route', function (): void {
    Route::get('/sign-in', fn (): string => 'Login')->name('custom.login');

    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->loginRoute('custom.login')
        ->authenticatables(PanelUser::class);

    app(PanelRegistry::class)->register($panel);

    try {
        app(AuthenticatePanel::class)->handle(
            Request::create('/admin'),
            fn (): Response => new Response('ok'),
            'admin',
        );
    } catch (AuthenticationException $exception) {
        expect($exception->redirectTo(Request::create('/admin')))->toBe('http://localhost/sign-in');

        return;
    }

    $this->fail('Expected an authentication exception.');
});

it('fails clearly when an authenticated panel login route is missing', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelUser::class);

    app(PanelRegistry::class)->register($panel);

    expect(fn () => app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(
        LogicException::class,
        'Panel [admin] requires authentication but no login route [admin.login] was registered.',
    );
});

it('allows authenticated users that match the configured authenticatable model', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelUser::class);

    app(PanelRegistry::class)->register($panel);
    $this->be(new PanelUser);

    $response = app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect($response->getContent())->toBe('ok');
});

it('denies authenticated users that do not match the configured authenticatable model', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelAdmin::class);

    app(PanelRegistry::class)->register($panel);
    $this->be(new PanelUser);

    expect(fn () => app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(AuthorizationException::class);
});

it('uses the model access contract when the authenticated model implements it', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authenticatables(PanelAdmin::class);

    app(PanelRegistry::class)->register($panel);
    $this->be(new PanelAdmin(canAccessPanels: false));

    expect(fn () => app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    ))->toThrow(AuthorizationException::class);
});

it('can resolve the authenticated user from a configured guard', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin')
        ->authGuard('admin')
        ->authenticatables(PanelAdmin::class);

    app(PanelRegistry::class)->register($panel);
    $this->be(new PanelUser, 'web');
    $this->be(new PanelAdmin, 'admin');

    $response = app(AuthenticatePanel::class)->handle(
        Request::create('/admin'),
        fn (): Response => new Response('ok'),
        'admin',
    );

    expect($response->getContent())->toBe('ok');
});

final class PublicPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('public')
            ->path('public')
            ->name('Public')
            ->routes(function (): void {
                Route::get('/', fn (): string => 'Home')->name('home');
            });
    }
}

final class AuthenticatedPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->name('Admin')
            ->authenticatables(PanelAdmin::class)
            ->routes(function (): void {
                Route::get('/', fn (): string => 'Home')->name('home');
            });
    }
}

final class PanelUser extends Authenticatable {}

final class PanelAdmin extends Authenticatable implements CanAccessPanel
{
    public function __construct(
        private readonly bool $canAccessPanels = true,
    ) {
        parent::__construct();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessPanels;
    }
}
