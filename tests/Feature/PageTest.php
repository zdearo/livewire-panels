<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Panel\Page;

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
