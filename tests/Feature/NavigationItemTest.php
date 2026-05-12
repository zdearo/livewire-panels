<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\NavigationItem;

it('configures a navigation item descriptor', function (): void {
    $item = NavigationItem::make('Inbox')
        ->url('/admin/inbox')
        ->icon('inbox')
        ->badge(12)
        ->group('Messages')
        ->sort(20);

    expect($item)
        ->label->toBe('Inbox')
        ->url->toBe('/admin/inbox')
        ->icon->toBe('inbox')
        ->badge->toBe('12')
        ->group->toBe('Messages')
        ->sort->toBe(20);
});

it('allows clearing a navigation item badge', function (): void {
    $item = NavigationItem::make('Inbox')->badge(null);

    expect($item->badge)->toBeNull();
});

it('does not mark navigation items without a usable path as current', function (): void {
    expect(NavigationItem::make('Inbox')->isCurrent())->toBeFalse()
        ->and(NavigationItem::make('Docs')->url('https://example.com')->isCurrent())->toBeFalse();
});

it('checks whether a navigation item matches the current request path', function (): void {
    expect(NavigationItem::make('Inbox')->url('/admin/inbox')->isCurrent())->toBeFalse();
});
