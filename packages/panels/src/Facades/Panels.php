<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Facades;

use Illuminate\Support\Facades\Facade;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Panel\PanelManager;

/**
 * @method static void setCurrentPanel(Panel $panel)
 * @method static Panel|null currentPanel()
 * @method static Panel panel(?string $id = null, bool $isStrict = true)
 * @method static Panel defaultPanel()
 * @method static array<string, Panel> panels()
 * @method static void setCurrentTenant(object|null $tenant)
 * @method static object|null currentTenant()
 * @method static array<string, mixed> tenantRouteParameters(Panel|null $panel = null)
 * @method static string route(string $name, array<string, mixed> $parameters = [], bool $absolute = true)
 *
 * @see PanelManager
 */
final class Panels extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return PanelManager::class;
    }
}
