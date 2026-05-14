<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Closure;
use Illuminate\Http\Request;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

final class NavigationItem
{
    /**
     * @var string|Closure(): string
     */
    public private(set) string|Closure $label;

    public private(set) ?string $url = null;

    public private(set) ?string $icon = null;

    public private(set) ?string $badge = null;

    public private(set) ?string $group = null;

    public private(set) int $sort = 0;

    /**
     * @param  string|Closure(): string  $label
     */
    public static function make(string|Closure $label): self
    {
        $item = new self;
        $item->label = $label;

        return $item;
    }

    public function displayLabel(): string
    {
        $label = $this->label instanceof Closure
            ? ($this->label)()
            : $this->label;

        $translation = __($label);

        return is_string($translation) ? $translation : $label;
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

        return $this->currentRequest()->is(ltrim($path, '/'));
    }

    private function currentRequest(): Request
    {
        return app(CurrentRequestResolver::class)->resolve(request());
    }
}
