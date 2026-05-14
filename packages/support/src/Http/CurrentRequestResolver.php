<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Http;

use Illuminate\Http\Request;

final class CurrentRequestResolver
{
    public function resolve(Request $fallback): Request
    {
        if (! ($fallback->is('livewire/*') || $fallback->is('livewire-unit-test-endpoint/*')) || ! app()->bound('originalRequest')) {
            return $fallback;
        }

        $originalRequest = app('originalRequest');

        if (! $originalRequest instanceof Request || $originalRequest === request()) {
            return $fallback;
        }

        return $originalRequest;
    }
}
