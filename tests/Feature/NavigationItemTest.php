<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Navigation\NavigationContract;
use Zdearo\LivewirePanels\Navigation\NavigationItem;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

it('configures a navigation item descriptor', function (): void {
    $item = NavigationItem::make('Inbox')
        ->url('/admin/inbox')
        ->icon('heroicon-o-inbox')
        ->badge(12)
        ->group('Messages')
        ->sort(20);

    expect($item)
        ->label->toBe('Inbox')
        ->url->toBe('/admin/inbox')
        ->icon->toBe('heroicon-o-inbox')
        ->badge->toBe('12')
        ->group->toBe('Messages')
        ->sort->toBe(20);
});

it('resolves navigation item labels lazily', function (): void {
    $item = NavigationItem::make(fn (): string => __('Inbox'));

    expect($item->displayLabel())->toBe('Inbox');
});

it('resolves navigation item urls and badges lazily', function (): void {
    $item = NavigationItem::make('Inbox')
        ->url(fn (): string => '/admin/inbox')
        ->badge(fn (): int => 12);

    expect($item)
        ->displayUrl()->toBe('/admin/inbox')
        ->displayBadge()->toBe('12');
});

it('uses a separate active URL when checking current state', function (): void {
    $item = NavigationItem::make('Leads')
        ->url('/leads/overview')
        ->activeUrl('/leads');

    expect($item)
        ->displayUrl()->toBe('/leads/overview')
        ->displayActiveUrl()->toBe('/leads')
        ->and($item->isCurrentFor(Request::create('/leads')))->toBeTrue()
        ->and($item->isCurrentFor(Request::create('/leads/settings')))->toBeTrue()
        ->and($item->isCurrentFor(Request::create('/leads/overview')))->toBeTrue();
});

it('falls back to the click URL when no active URL is configured', function (): void {
    $item = NavigationItem::make('Leads')
        ->url('/leads/overview');

    expect($item)
        ->displayActiveUrl()->toBe('/leads/overview')
        ->and($item->isCurrentFor(Request::create('/leads')))->toBeFalse()
        ->and($item->isCurrentFor(Request::create('/leads/overview')))->toBeTrue();
});

it('fails when lazy navigation item values resolve to unsupported types', function (): void {
    expect(fn () => NavigationItem::make(fn (): array => [])->displayLabel())
        ->toThrow(UnexpectedValueException::class, 'Navigation item labels must resolve to strings.')
        ->and(fn () => NavigationItem::make('Inbox')->url(fn (): array => [])->displayUrl())
        ->toThrow(UnexpectedValueException::class, 'Navigation item URLs must resolve to strings or null.')
        ->and(fn () => NavigationItem::make('Inbox')->activeUrl(fn (): array => [])->displayActiveUrl())
        ->toThrow(UnexpectedValueException::class, 'Navigation item active URLs must resolve to strings or null.')
        ->and(fn () => NavigationItem::make('Inbox')->badge(fn (): array => [])->displayBadge())
        ->toThrow(UnexpectedValueException::class, 'Navigation item badges must resolve to strings, integers, or null.');
});

it('allows clearing a navigation item badge', function (): void {
    $item = NavigationItem::make('Inbox')->badge(null);

    expect($item->badge)
        ->toBeNull()
        ->and($item->displayBadge())->toBeNull();
});

it('resolves navigation item visibility lazily', function (): void {
    expect(NavigationItem::make('Inbox')->visible(fn (): bool => false)->isVisible())->toBeFalse()
        ->and(NavigationItem::make('Inbox')->hidden(fn (): bool => true)->isVisible())->toBeFalse()
        ->and(NavigationItem::make('Inbox')->visible(fn (): bool => true)->hidden(fn (): bool => false)->isVisible())->toBeTrue();
});

it('uses spa navigation unless the item overrides it', function (): void {
    expect(NavigationItem::make('Inbox')->usesSpaNavigation())->toBeTrue()
        ->and(NavigationItem::make('Inbox')->usesSpaNavigation(default: false))->toBeFalse()
        ->and(NavigationItem::make('Inbox')->spa()->usesSpaNavigation(default: false))->toBeTrue()
        ->and(NavigationItem::make('Inbox')->spa(false)->usesSpaNavigation())->toBeFalse();
});

it('does not mark navigation items without a usable path as current', function (): void {
    expect(NavigationItem::make('Inbox')->isCurrent())->toBeFalse()
        ->and(NavigationItem::make('Docs')->url('https://example.com')->isCurrent())->toBeFalse();
});

it('does not mark a root URL navigation item current on descendant paths', function (): void {
    expect(NavigationItem::make('Home')->url('/')->isCurrentFor(Request::create('/admin/datasets/1')))->toBeFalse();
});

it('resolves the root navigation item as current for the root request', function (): void {
    $item = NavigationItem::make('Home')->url('/');
    $navigation = new NavigationContract([$item], []);

    expect($navigation->currentItemFor(Request::create('/')))->toBe($item);
});

it('checks whether a navigation item matches the current request path', function (): void {
    expect(NavigationItem::make('Inbox')->url('/admin/inbox')->isCurrent())->toBeFalse();
});

it('checks whether a navigation item matches the original request path when available', function (): void {
    app()->instance('request', Request::create('/livewire/update'));
    app()->instance('originalRequest', Request::create('/admin/inbox'));

    expect(NavigationItem::make('Inbox')->url('/admin/inbox')->isCurrent())->toBeTrue();
});

it('marks section navigation items current on descendant page paths', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        Panel::make()
            ->id('admin')
            ->path('admin'),
    );

    app()->instance('request', Request::create('/admin/datasets/1'));

    expect(NavigationItem::make('Datasets')->url('/admin/datasets')->isCurrent())->toBeTrue();
});

it('does not mark the panel root navigation item current on descendant page paths', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        Panel::make()
            ->id('admin')
            ->path('admin'),
    );

    app()->instance('request', Request::create('/admin/datasets/1'));

    expect(NavigationItem::make('Dashboard')->url('/admin')->isCurrent())->toBeFalse();
});

it('does not mark a root panel navigation item current on descendant page paths', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        Panel::make()
            ->id('admin')
            ->path(''),
    );

    app()->instance('request', Request::create('/datasets/1'));

    expect(NavigationItem::make('Dashboard')->url('/')->isCurrent())->toBeFalse();
});

it('marks section navigation items current on descendant page paths for root panels', function (): void {
    app(PanelManager::class)->setCurrentPanel(
        Panel::make()
            ->id('admin')
            ->path(''),
    );

    app()->instance('request', Request::create('/datasets/1'));

    expect(NavigationItem::make('Datasets')->url('/datasets')->isCurrent())->toBeTrue();
});
