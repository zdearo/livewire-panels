<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Routing;

final class RouteSegments
{
    public static function path(string ...$paths): string
    {
        $segments = [];

        foreach ($paths as $path) {
            $path = trim($path, '/');

            if ($path !== '') {
                $segments[] = $path;
            }
        }

        $path = implode('/', $segments);

        return $path === '' ? '/' : "/{$path}";
    }

    public static function name(?string ...$names): string
    {
        $segments = [];

        foreach ($names as $name) {
            if ($name !== null && $name !== '') {
                $segments[] = $name;
            }
        }

        return implode('.', $segments);
    }
}
