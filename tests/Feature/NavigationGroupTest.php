<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Navigation\NavigationGroup;
use Zdearo\LivewirePanels\Navigation\NavigationItem;

it('configures a navigation group descriptor', function (): void {
    $group = NavigationGroup::make('management')
        ->label('Management')
        ->icon('briefcase')
        ->sort(20);

    expect($group)
        ->id->toBe('management')
        ->label->toBe('Management')
        ->icon->toBe('briefcase')
        ->sort->toBe(20)
        ->items->toBe([]);
});

it('uses a headline label by default', function (): void {
    $group = NavigationGroup::make('support-tools');

    expect($group->label)->toBe('Support Tools');
});

it('resolves navigation group labels lazily', function (): void {
    $group = NavigationGroup::make('management')
        ->label(fn (): string => __('Management'));

    expect($group->displayLabel())->toBe('Management');
});

it('can receive navigation items in sorted order', function (): void {
    $group = NavigationGroup::make('management')
        ->addItem(NavigationItem::make('Users')->sort(20))
        ->addItem(NavigationItem::make('Dashboard')->sort(10))
        ->sortItems();

    expect($group->items)
        ->sequence(
            fn ($item) => $item->label->toBe('Dashboard'),
            fn ($item) => $item->label->toBe('Users'),
        );
});
