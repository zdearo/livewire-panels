<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

it('returns the fallback request outside livewire updates', function (): void {
    $fallback = Request::create('/admin/users');

    app()->instance('originalRequest', Request::create('/admin/inbox'));

    expect(app(CurrentRequestResolver::class)->resolve($fallback))->toBe($fallback);
});

it('returns the original request during livewire updates', function (): void {
    $fallback = Request::create('/livewire/update');
    $original = Request::create('/admin/inbox');

    app()->instance('originalRequest', $original);

    expect(app(CurrentRequestResolver::class)->resolve($fallback))->toBe($original);
});

it('returns the fallback livewire request when the original request is not available', function (): void {
    $fallback = Request::create('/livewire/update');

    expect(app(CurrentRequestResolver::class)->resolve($fallback))->toBe($fallback);
});

it('returns the fallback livewire request when the original request is invalid', function (): void {
    $fallback = Request::create('/livewire/update');

    app()->instance('originalRequest', 'not-a-request');

    expect(app(CurrentRequestResolver::class)->resolve($fallback))->toBe($fallback);
});
