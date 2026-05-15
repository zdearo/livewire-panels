<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Facades;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Facade;
use Zdearo\LivewirePanels\Icons\PanelsIconManager;

/**
 * @method static void register(array<string, string|Htmlable> $icons)
 * @method static string|Htmlable resolve(string $alias)
 *
 * @see PanelsIconManager
 */
final class PanelsIcon extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return PanelsIconManager::class;
    }
}
