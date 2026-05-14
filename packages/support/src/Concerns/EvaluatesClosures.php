<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Concerns;

use Closure;

trait EvaluatesClosures
{
    /**
     * @param  array<int, mixed>  $parameters
     */
    protected function evaluate(mixed $value, array $parameters = []): mixed
    {
        if (! $value instanceof Closure) {
            return $value;
        }

        return $value(...$parameters);
    }
}
