<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Panel\Page;
use Zdearo\LivewirePanels\Panel\PageGroup;

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
        ->navigation('Users', icon: 'users', group: 'Management', sort: 20);

    expect($page->navigation)
        ->label->toBe('Users')
        ->icon->toBe('users')
        ->group->toBe('Management')
        ->sort->toBe(20);
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
