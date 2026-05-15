<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Page\Page;
use Zdearo\LivewirePanels\Page\PageGroup;

it('configures a panel page descriptor', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->name('users');

    expect($page)
        ->path->toBe('/users')
        ->component->toBe('pages::admin.users')
        ->name->toBe('users');
});

it('does not add panel pages to the navigation by default', function (): void {
    $page = Page::make('/users', 'pages::admin.users');

    expect($page->navigation)->toBeNull();
});

it('can configure a panel page navigation item', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation('Users', icon: 'heroicon-o-users', group: 'Management', sort: 20);

    expect($page->navigation)
        ->label->toBe('Users')
        ->icon->toBe('heroicon-o-users')
        ->group->toBe('Management')
        ->sort->toBe(20);
});

it('can configure a panel page navigation item with a lazy label', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation(fn (): string => __('Users'));

    expect($page->navigation?->displayLabel())->toBe('Users');
});

it('can create a page group from the page descriptor API', function (): void {
    $group = Page::group('/settings')
        ->name('settings')
        ->pages([
            Page::make('/', 'pages::admin.settings.index')->name('index'),
            Page::make('/users', 'pages::admin.settings.users')->name('users'),
        ]);

    expect($group)
        ->toBeInstanceOf(PageGroup::class)
        ->path->toBe('/settings')
        ->name->toBe('settings')
        ->pages->toHaveCount(2)
        ->sequence(
            fn ($page) => $page
                ->toBeInstanceOf(Page::class)
                ->path->toBe('/')
                ->component->toBe('pages::admin.settings.index')
                ->name->toBe('index'),
            fn ($page) => $page
                ->toBeInstanceOf(Page::class)
                ->path->toBe('/users')
                ->component->toBe('pages::admin.settings.users')
                ->name->toBe('users'),
        );
});
