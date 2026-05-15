<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Http;

use Illuminate\Http\Request;

final class CurrentRequestResolver
{
    public function resolve(Request $fallback): Request
    {
        if (! $this->isLivewireUpdateRequest($fallback) || ! app()->bound('originalRequest')) {
            return $fallback;
        }

        $originalRequest = app('originalRequest');

        if (! $originalRequest instanceof Request || $originalRequest === request()) {
            return $fallback;
        }

        return $originalRequest;
    }

    private function isLivewireUpdateRequest(Request $request): bool
    {
        return $request->is('livewire/*')
            || $request->is('livewire-*/*')
            || $request->is('livewire-unit-test-endpoint/*');
    }
}
