<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Navigation;

use Closure;
use Illuminate\Support\Str;
use UnexpectedValueException;
use Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures;

final class NavigationGroup
{
    use EvaluatesClosures;

    public private(set) string $id;

    /**
     * @var string|Closure(): string
     */
    public private(set) string|Closure $label;

    public private(set) ?string $icon = null;

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
     * @var array<int, NavigationItem>
     */
    public private(set) array $items = [];

    public static function make(string $id): self
    {
        $group = new self;
        $group->id = $id;
        $group->label = (string) Str::of($id)->replace(['-', '_'], ' ')->headline();

        return $group;
    }

    /**
     * @param  string|Closure(): string  $label
     */
    public function label(string|Closure $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function displayLabel(): string
    {
        $label = $this->evaluate($this->label);

        if (! is_string($label)) {
            throw new UnexpectedValueException('Navigation group labels must resolve to strings.');
        }

        $translation = __($label);

        return is_string($translation) ? $translation : $label;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

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

    public function addItem(NavigationItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function clearItems(): self
    {
        $this->items = [];

        return $this;
    }

    public function sortItems(): self
    {
        usort(
            $this->items,
            fn (NavigationItem $first, NavigationItem $second): int => $first->sort <=> $second->sort,
        );

        return $this;
    }
}
