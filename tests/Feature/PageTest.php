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

it('can configure whether a panel page navigation item uses spa navigation', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation('Users', spa: false);

    expect($page->navigation?->usesSpaNavigation())->toBeFalse();
});

it('can configure a panel page navigation click URL without defining active state', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation('Users')
        ->navigationUrl(fn (): string => '/admin/users/overview');

    expect($page->navigation)
        ->url->toBeInstanceOf(Closure::class)
        ->displayUrl()->toBe('/admin/users/overview')
        ->displayActiveUrl()->toBe('/admin/users/overview');
});

it('can configure panel page navigation visibility from the page descriptor', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation('Users')
        ->visible(fn (): bool => false);

    expect($page->navigation?->isVisible())->toBeFalse();
});

it('can configure panel page navigation hidden state from the page descriptor', function (): void {
    $page = Page::make('/users', 'pages::admin.users')
        ->navigation('Users')
        ->hidden(fn (): bool => true);

    expect($page->navigation?->isVisible())->toBeFalse();
});

it('requires page navigation before configuring page navigation visibility', function (): void {
    expect(fn () => Page::make('/users', 'pages::admin.users')->visible())
        ->toThrow(LogicException::class, 'Page navigation must be configured before visibility can be configured.');
});

it('requires page navigation before configuring a page navigation click URL', function (): void {
    expect(fn () => Page::make('/users', 'pages::admin.users')->navigationUrl('/admin/users/overview'))
        ->toThrow(LogicException::class, 'Page navigation must be configured before its URL can be configured.');
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
