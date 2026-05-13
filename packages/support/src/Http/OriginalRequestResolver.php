<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Http;

use Illuminate\Http\Request;
use Livewire\Livewire;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

use function Livewire\invade;

final readonly class OriginalRequestResolver
{
    public function __construct(
        private PersistentMiddleware $persistentMiddleware,
    ) {}

    public function resolve(): Request
    {
        if (! Livewire::isLivewireRequest()) {
            return request();
        }

        /** @phpstan-ignore-next-line */
        $request = invade($this->persistentMiddleware)->makeFakeRequest();

        /** @phpstan-ignore-next-line */
        invade($this->persistentMiddleware)->getRouteFromRequest($request);

        return $request instanceof Request ? $request : request();
    }
}
