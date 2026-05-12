<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

final class Page
{
    public private(set) string $path;

    public private(set) string $component;

    public private(set) ?string $name = null;

    public static function make(string $path, string $component): self
    {
        $page = new self;
        $page->path = $path;
        $page->component = $component;

        return $page;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
