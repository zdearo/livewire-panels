<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Auth\Contracts;

use Zdearo\LivewirePanels\Panel\Panel;

interface CanAccessPanel
{
    public function canAccessPanel(Panel $panel): bool;
}
