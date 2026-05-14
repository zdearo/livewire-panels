<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Support\Routing\RouteSegments;

it('joins route path segments', function (): void {
    expect(RouteSegments::path('admin', '/settings/', 'users'))
        ->toBe('/admin/settings/users')
        ->and(RouteSegments::path('/', '', '/'))
        ->toBe('/');
});

it('joins route name segments', function (): void {
    expect(RouteSegments::name('admin', null, '', 'users'))
        ->toBe('admin.users')
        ->and(RouteSegments::name(null, ''))
        ->toBe('');
});
