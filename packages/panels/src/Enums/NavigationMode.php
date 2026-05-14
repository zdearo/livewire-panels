<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Enums;

enum NavigationMode: string
{
    case Sidebar = 'sidebar';
    case Topbar = 'topbar';
    case TopbarWithSidebar = 'topbar-sidebar';
}
