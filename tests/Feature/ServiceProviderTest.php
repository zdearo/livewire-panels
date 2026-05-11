<?php

declare(strict_types=1);

it('registers the default package configuration', function (): void {
    expect(config('livewire-panels.providers'))->toBe([]);
});
