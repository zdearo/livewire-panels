<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels;

use Illuminate\Support\Arr;
use LogicException;

final class Panel
{
    public private(set) string $id;

    public private(set) string $path;

    public private(set) string $name;

    public private(set) bool $isDefault = false;

    /**
     * @var array<int, string>
     */
    public private(set) array $middleware = [];

    public static function make(): self
    {
        return app(self::class);
    }

    public function id(string $id): self
    {
        if (isset($this->id)) {
            throw new LogicException("The panel already has the ID [{$this->id}].");
        }

        $this->id = $id;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  array<int, string>|string  $middleware
     */
    public function middleware(array|string $middleware): self
    {
        $this->middleware = Arr::wrap($middleware);

        return $this;
    }

    public function default(bool $condition = true): self
    {
        $this->isDefault = $condition;

        return $this;
    }
}
