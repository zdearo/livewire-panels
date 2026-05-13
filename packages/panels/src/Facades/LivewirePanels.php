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
 *
 * @see PanelManager
 */
final class LivewirePanels extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return PanelManager::class;
    }
}
