<?php

declare(strict_types=1);

it('does not register the removed panel sidebar component', function (): void {
    expect(app('livewire')->exists('livewire-panels::panel-sidebar'))->toBeFalse();
});
