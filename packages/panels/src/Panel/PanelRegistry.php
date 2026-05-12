<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Panel;

use Illuminate\Support\Arr;
use RuntimeException;

final class PanelRegistry
{
    /**
     * @var array<string, Panel>
     */
    private array $panels = [];

    public function register(Panel $panel): void
    {
        $this->panels[$panel->id] = $panel;
    }

    public function get(?string $id = null, bool $isStrict = true): Panel
    {
        return $this->find($id, $isStrict) ?? $this->getDefault();
    }

    public function getDefault(): Panel
    {
        return Arr::first(
            $this->panels,
            fn (Panel $panel): bool => $panel->isDefault,
            fn () => throw new RuntimeException('No default panel found.'),
        );
    }

    protected function find(?string $id = null, bool $isStrict = true): ?Panel
    {
        if ($id === null) {
            return null;
        }

        if ($isStrict) {
            return $this->panels[$id] ?? null;
        }

        $panels = [];

        foreach ($this->panels as $key => $panel) {
            $panels[$this->normalizeId($key)] = $panel;
        }

        return $panels[$this->normalizeId($id)] ?? null;
    }

    protected function normalizeId(string $id): string
    {
        return (string) str($id)
            ->lower()
            ->replace(['-', '_'], '');
    }

    /**
     * @return array<string, Panel>
     */
    public function all(): array
    {
        return $this->panels;
    }
}
