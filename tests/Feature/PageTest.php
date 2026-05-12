<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Page;

it('configures a panel page descriptor', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->name('users');

    expect($page)
        ->path->toBe('/users')
        ->component->toBe('pages::admin.users')
        ->name->toBe('users');
});
