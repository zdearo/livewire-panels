<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Shell;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Zdearo\LivewirePanels\Panel\Panel;

abstract class PanelShell
{
    public function sidebarBrand(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }

    public function topbarBrand(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }

    public function mobileSidebarBrand(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }

    public function sidebarFooter(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }

    public function topbarEnd(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }

    public function mobileHeaderEnd(Panel $panel): View|Htmlable|string|null
    {
        return null;
    }
}
