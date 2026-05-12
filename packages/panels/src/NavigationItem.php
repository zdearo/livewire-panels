<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

final class NavigationItem
{
    public private(set) string $label;

    public private(set) ?string $url = null;

    public private(set) ?string $icon = null;

    public private(set) ?string $badge = null;

    public private(set) ?string $group = null;

    public private(set) int $sort = 0;

    public static function make(string $label): self
    {
        $item = new self;
        $item->label = $label;

        return $item;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function badge(int|string|null $badge): self
    {
        $this->badge = $badge === null ? null : (string) $badge;

        return $this;
    }

    public function group(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function sort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function isCurrent(): bool
    {
        if ($this->url === null) {
            return false;
        }

        $path = parse_url($this->url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return false;
        }

        return request()->is(ltrim($path, '/'));
    }
}
