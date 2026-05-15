<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Navigation\NavigationItem;

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

it('fails when lazy navigation item values resolve to unsupported types', function (): void {
    expect(fn () => NavigationItem::make(fn (): array => [])->displayLabel())
        ->toThrow(UnexpectedValueException::class, 'Navigation item labels must resolve to strings.')
        ->and(fn () => NavigationItem::make('Inbox')->url(fn (): array => [])->displayUrl())
        ->toThrow(UnexpectedValueException::class, 'Navigation item URLs must resolve to strings or null.')
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

it('does not mark navigation items without a usable path as current', function (): void {
    expect(NavigationItem::make('Inbox')->isCurrent())->toBeFalse()
        ->and(NavigationItem::make('Docs')->url('https://example.com')->isCurrent())->toBeFalse();
});

it('checks whether a navigation item matches the current request path', function (): void {
    expect(NavigationItem::make('Inbox')->url('/admin/inbox')->isCurrent())->toBeFalse();
});

it('checks whether a navigation item matches the original request path when available', function (): void {
    app()->instance('request', Request::create('/livewire/update'));
    app()->instance('originalRequest', Request::create('/admin/inbox'));

    expect(NavigationItem::make('Inbox')->url('/admin/inbox')->isCurrent())->toBeTrue();
});
