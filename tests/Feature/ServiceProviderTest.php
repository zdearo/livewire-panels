<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('does not register panel providers through package configuration', function (): void {
    expect(config('livewire-panels'))->toBeNull();
});

it('loads the package app layout views', function (): void {
    expect(view()->exists('livewire-panels::layouts.app'))->toBeTrue();
});

it('loads the package panel layout views', function (): void {
    expect(view()->exists('livewire-panels::layouts.panel'))->toBeTrue();
});

it('wraps the panel layout with the package app layout', function (): void {
    $html = Blade::render('<x-livewire-panels::layouts.panel>Panel body</x-livewire-panels::layouts.panel>');

    expect($html)
        ->toContain('<!DOCTYPE html>')
        ->toContain('data-livewire-panels-layout="app"')
        ->toContain('data-livewire-panels-layout="panel"')
        ->toContain('Panel body');
});
