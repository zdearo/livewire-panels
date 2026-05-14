<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Shell\DefaultPanelShell;
use Zdearo\LivewirePanels\Shell\FluxPanelShell;
use Zdearo\LivewirePanels\Shell\PanelShell;

it('allows the default panel shell to be extended by applications', function (): void {
    expect(new ReflectionClass(DefaultPanelShell::class)->isFinal())->toBeFalse()
        ->and(new ReflectionClass(DefaultPanelShell::class)->isAbstract())->toBeTrue()
        ->and(app(DefaultPanelShell::class))->toBeInstanceOf(FluxPanelShell::class);
});

it('returns null from the base panel shell hooks', function (): void {
    $shell = new class extends PanelShell {};
    $panel = shellTestingPanel();

    expect($shell)
        ->sidebarBrand($panel)->toBeNull()
        ->topbarBrand($panel)->toBeNull()
        ->mobileSidebarBrand($panel)->toBeNull()
        ->sidebarFooter($panel)->toBeNull()
        ->topbarEnd($panel)->toBeNull()
        ->mobileHeaderEnd($panel)->toBeNull();
});

it('does not render a default user menu when the authenticated panel has no user', function (): void {
    $panel = shellTestingPanel()->authenticatables(PanelShellUser::class);

    expect(app(DefaultPanelShell::class)->sidebarFooter($panel))->toBeNull();
});

it('uses the auth identifier as the default user menu name when the user has no name', function (): void {
    $panel = shellTestingPanel()->authenticatables(PanelShellUser::class);
    $this->be((new PanelShellUser)->forceFill(['id' => 42]));

    $menu = app(DefaultPanelShell::class)->sidebarFooter($panel);

    expect($menu?->render())->toContain('42');
});

it('falls back to the user class basename when the auth identifier is not stringable', function (): void {
    $panel = shellTestingPanel()->authenticatables(PanelShellObjectIdentifierUser::class);
    $this->be(new PanelShellObjectIdentifierUser);

    $menu = app(DefaultPanelShell::class)->sidebarFooter($panel);

    expect($menu?->render())->toContain('PanelShellObjectIdentifierUser');
});

it('uses the first navigation url as the default brand url', function (): void {
    $panel = shellTestingPanel()
        ->navigation(NavigationItem::make('Dashboard')->url('/admin/tenants/acme'));

    $brand = app(DefaultPanelShell::class)->sidebarBrand($panel);

    expect($brand->render())->toContain('href="/admin/tenants/acme"');
});

it('uses the first grouped navigation url as the default brand url', function (): void {
    $panel = shellTestingPanel()
        ->navigationGroups(NavigationGroup::make('management'))
        ->navigation(NavigationItem::make('Users')->url('/admin/tenants/acme/users')->group('management'));

    $brand = app(DefaultPanelShell::class)->sidebarBrand($panel);

    expect($brand->render())->toContain('href="/admin/tenants/acme/users"');
});

function shellTestingPanel(): Panel
{
    return Panel::make()
        ->id('admin')
        ->path('admin')
        ->name('Admin');
}

final class PanelShellUser extends Authenticatable {}

final class PanelShellObjectIdentifierUser implements AuthenticatableContract
{
    #[Override]
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    #[Override]
    public function getAuthIdentifier(): object
    {
        return new stdClass;
    }

    #[Override]
    public function getAuthPassword(): ?string
    {
        return null;
    }

    #[Override]
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    #[Override]
    public function getRememberToken(): ?string
    {
        return null;
    }

    #[Override]
    public function setRememberToken($value): void {}

    #[Override]
    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
