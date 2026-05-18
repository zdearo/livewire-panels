<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Closure;
use Illuminate\Http\Request;
use UnexpectedValueException;
use Zdearo\LivewirePanels\Facades\Panels;
use Zdearo\LivewirePanels\Panel\Panel;
use Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures;
use Zdearo\LivewirePanels\Support\Http\CurrentRequestResolver;

final class NavigationItem
{
    use EvaluatesClosures;

    /**
     * @var string|Closure(): string
     */
    public private(set) string|Closure $label;

    /**
     * @var string|Closure(): string|null
     */
    public private(set) string|Closure|null $url = null;

    public private(set) ?string $icon = null;

    /**
     * @var string|Closure(): (int|string|null)|null
     */
    public private(set) string|Closure|null $badge = null;

    public private(set) ?string $group = null;

    public private(set) int $sort = 0;

    /**
     * @var bool|Closure(): bool
     */
    public private(set) bool|Closure $isVisible = true;

    /**
     * @var bool|Closure(): bool
     */
    public private(set) bool|Closure $isHidden = false;

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
        $label = $this->evaluate($this->label);

        if (! is_string($label)) {
            throw new UnexpectedValueException('Navigation item labels must resolve to strings.');
        }

        $translation = __($label);

        return is_string($translation) ? $translation : $label;
    }

    /**
     * @param  string|Closure(): string|null  $url
     */
    public function url(string|Closure|null $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function displayUrl(): ?string
    {
        $url = $this->evaluate($this->url);

        if ($url === null || is_string($url)) {
            return $url;
        }

        throw new UnexpectedValueException('Navigation item URLs must resolve to strings or null.');
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param  int|string|Closure(): (int|string|null)|null  $badge
     */
    public function badge(int|string|Closure|null $badge): self
    {
        $this->badge = $badge instanceof Closure || $badge === null ? $badge : (string) $badge;

        return $this;
    }

    public function displayBadge(): ?string
    {
        $badge = $this->evaluate($this->badge);

        if ($badge === null) {
            return null;
        }

        if (is_int($badge) || is_string($badge)) {
            return (string) $badge;
        }

        throw new UnexpectedValueException('Navigation item badges must resolve to strings, integers, or null.');
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

    /**
     * @param  bool|Closure(): bool  $condition
     */
    public function visible(bool|Closure $condition = true): self
    {
        $this->isVisible = $condition;

        return $this;
    }

    /**
     * @param  bool|Closure(): bool  $condition
     */
    public function hidden(bool|Closure $condition = true): self
    {
        $this->isHidden = $condition;

        return $this;
    }

    public function isVisible(): bool
    {
        return (bool) $this->evaluate($this->isVisible)
            && ! (bool) $this->evaluate($this->isHidden);
    }

    public function isCurrent(): bool
    {
        return $this->isCurrentFor(
            $this->currentRequest(),
            Panels::currentPanel(),
        );
    }

    public function isCurrentFor(Request $request, ?Panel $panel = null): bool
    {
        $url = $this->displayUrl();

        if ($url === null) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return false;
        }

        $path = ltrim($path, '/');

        if ($request->is($path)) {
            return true;
        }

        if (! $this->canMatchDescendantPaths($path, $panel)) {
            return false;
        }

        return $request->is($path.'/*');
    }

    private function currentRequest(): Request
    {
        return app(CurrentRequestResolver::class)->resolve(request());
    }

    private function canMatchDescendantPaths(string $path, ?Panel $panel): bool
    {
        if ($path === '') {
            return false;
        }

        if ($panel === null) {
            return true;
        }

        return $this->segmentCount($path) > $this->segmentCount($panel->path);
    }

    private function segmentCount(string $path): int
    {
        $path = trim($path, '/');

        if ($path === '') {
            return 0;
        }

        return count(explode('/', $path));
    }
}
