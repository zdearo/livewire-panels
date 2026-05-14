<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Page;

final class PageGroup
{
    public private(set) string $path;

    public private(set) ?string $name = null;

    /**
     * @var array<int, Page|PageGroup>
     */
    public private(set) array $pages = [];

    public static function make(string $path): self
    {
        $group = new self;
        $group->path = $path;

        return $group;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function page(Page|PageGroup $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * @param  array<int, Page|PageGroup>  $pages
     */
    public function pages(array $pages): self
    {
        foreach ($pages as $page) {
            $this->page($page);
        }

        return $this;
    }
}
