<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class MakePanelPageCommand extends Command
{
    protected $signature = 'make:panel-page
        {component? : The Livewire page component name}
        {--path= : The panel route path}
        {--name= : The panel route name}
        {--sfc : Create a single-file Livewire component}
        {--mfc : Create a multi-file Livewire component}
        {--class : Create a class-based Livewire component}
        {--type= : Livewire component type: sfc, mfc, or class}
        {--test : Create a Livewire component test}
        {--js : Create a JavaScript file for multi-file components}
        {--css : Create CSS files for multi-file components}
        {--emoji= : Use emoji in Livewire file or directory names}';

    protected $description = 'Create a Livewire page component for a panel';

    public function handle(): int
    {
        $component = $this->componentName();
        $livewireComponent = $this->livewireComponentName($component);

        $result = $this->call('make:livewire', $this->livewireArguments($livewireComponent));

        if ($result !== self::SUCCESS) {
            return $result;
        }

        $this->line(sprintf(
            'Register it in your panel with: Page::make(%s, %s)->name(%s)',
            $this->quote($this->pagePath($component)),
            $this->quote($livewireComponent),
            $this->quote($this->pageName($component)),
        ));

        return self::SUCCESS;
    }

    private function componentName(): string
    {
        $component = $this->argument('component') ?? $this->ask('What is the page component name?');

        assert(is_string($component));

        return (string) Str::of($component)
            ->replace('/', '.')
            ->replace('\\', '.')
            ->replace('pages::', '')
            ->explode('.')
            ->filter()
            ->map(fn (string $segment): string => (string) Str::of($segment)->kebab())
            ->implode('.');
    }

    private function livewireComponentName(string $component): string
    {
        return 'pages::'.$component;
    }

    private function pagePath(string $component): string
    {
        $path = $this->option('path');

        if (is_string($path) && $path !== '') {
            return $path;
        }

        return '/'.Str::of($component)->afterLast('.')->kebab();
    }

    private function pageName(string $component): string
    {
        $name = $this->option('name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return (string) Str::of($component)->afterLast('.')->kebab();
    }

    /**
     * @return array<string, bool|string|null>
     */
    private function livewireArguments(string $component): array
    {
        $arguments = [
            'name' => $component,
        ];

        foreach (['sfc', 'mfc', 'class', 'test', 'js', 'css'] as $option) {
            if ($this->option($option)) {
                $arguments["--{$option}"] = true;
            }
        }

        foreach (['type', 'emoji'] as $option) {
            $value = $this->option($option);

            if (is_string($value) && $value !== '') {
                $arguments["--{$option}"] = $value;
            }
        }

        return $arguments;
    }

    private function quote(string $value): string
    {
        return var_export($value, true);
    }
}
