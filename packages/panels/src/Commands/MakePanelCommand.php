<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Str;

final class MakePanelCommand extends Command
{
    protected $signature = 'make:panel
        {id? : The panel identifier}
        {--path= : The panel route path}
        {--name= : The panel display name}
        {--middleware=* : The panel route middleware}
        {--default : Mark the panel as the default panel}
        {--shell : Create a custom panel shell class}
        {--force : Overwrite the panel provider if it already exists}';

    protected $description = 'Create a new Livewire panel provider';

    public function handle(Filesystem $files): int
    {
        $panelId = $this->panelId();
        $providerClass = $this->providerClass($panelId);
        $providerPath = $this->laravel->basePath("app/Providers/{$providerClass}.php");
        $stylesheetPath = $this->stylesheetPath($panelId);
        $vite = $this->vite($panelId);
        $shouldCreateShell = $this->shouldCreateShell();
        $shellClass = $this->shellClass($panelId);
        $shellPath = $this->shellPath($panelId);

        if ($files->exists($providerPath) && ! $this->option('force')) {
            $this->error("Panel provider [{$providerClass}] already exists.");

            return self::FAILURE;
        }

        if ($files->exists($stylesheetPath) && ! $this->option('force')) {
            $this->error("Panel stylesheet [{$vite}] already exists.");

            return self::FAILURE;
        }

        if ($shouldCreateShell && $files->exists($shellPath) && ! $this->option('force')) {
            $this->error("Panel shell [{$shellClass}] already exists.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($providerPath));
        $files->ensureDirectoryExists(dirname($stylesheetPath));

        if ($shouldCreateShell) {
            $files->ensureDirectoryExists(dirname($shellPath));
        }

        $files->put(
            $providerPath,
            $this->providerContents(
                stub: $files->get($this->stubPath()),
                providerClass: $providerClass,
                panelId: $panelId,
                path: $this->panelPath($panelId),
                name: $this->panelName($panelId),
                vite: $vite,
                middleware: $this->middleware(),
                isDefault: $this->isDefaultPanel($providerClass),
                shellClass: $shellClass,
                shouldCreateShell: $shouldCreateShell,
            ),
        );

        if ($shouldCreateShell) {
            $files->put(
                $shellPath,
                $this->shellContents(
                    stub: $files->get($this->shellStubPath()),
                    panelId: $panelId,
                    shellClass: $shellClass,
                ),
            );
        }

        $files->put($stylesheetPath, $files->get($this->stylesheetStubPath()));
        $this->addViteInput($files, $vite);

        LaravelServiceProvider::addProviderToBootstrapFile(
            $this->providerNamespace().'\\'.$providerClass,
            $this->laravel->basePath('bootstrap/providers.php'),
        );

        $this->info("Panel provider [{$providerClass}] created successfully.");

        return self::SUCCESS;
    }

    private function panelId(): string
    {
        $id = $this->argument('id') ?? $this->ask('What is the panel id?');

        assert(is_string($id));

        return (string) Str::of($id)->trim()->kebab()->lower();
    }

    private function providerClass(string $panelId): string
    {
        return Str::studly($panelId).'PanelProvider';
    }

    private function shellClass(string $panelId): string
    {
        return Str::studly($panelId).'PanelShell';
    }

    private function shellPath(string $panelId): string
    {
        return $this->laravel->basePath('app/Panels/'.$this->panelStudly($panelId).'/'.$this->shellClass($panelId).'.php');
    }

    private function stylesheetPath(string $panelId): string
    {
        return $this->laravel->resourcePath("css/panels/{$panelId}.css");
    }

    private function vite(string $panelId): string
    {
        return "resources/css/panels/{$panelId}.css";
    }

    private function shouldCreateShell(): bool
    {
        if ($this->option('shell')) {
            return true;
        }

        if ($this->argument('id') === null) {
            return $this->confirm('Create a custom panel shell class?', false);
        }

        return false;
    }

    private function panelPath(string $panelId): string
    {
        $path = $this->option('path');

        return is_string($path) && $path !== '' ? $path : $panelId;
    }

    private function panelName(string $panelId): string
    {
        $name = $this->option('name');

        return is_string($name) && $name !== ''
            ? $name
            : (string) Str::of($panelId)->replace('-', ' ')->headline();
    }

    /**
     * @return list<string>
     */
    private function middleware(): array
    {
        $middleware = [];

        foreach (Arr::wrap($this->option('middleware')) as $value) {
            if (is_string($value)) {
                foreach (explode(',', $value) as $item) {
                    $item = trim($item);

                    if ($item !== '') {
                        $middleware[] = $item;
                    }
                }
            }
        }

        return $middleware === [] ? ['web'] : array_values(array_unique($middleware));
    }

    private function isDefaultPanel(string $providerClass): bool
    {
        if ($this->option('default')) {
            return true;
        }

        return ! $this->hasOtherPanelProviders($providerClass);
    }

    private function hasOtherPanelProviders(string $providerClass): bool
    {
        $providerFiles = glob($this->laravel->basePath('app/Providers/*PanelProvider.php')) ?: [];

        return array_any(
            $providerFiles,
            fn (string $providerFile): bool => basename($providerFile) !== "{$providerClass}.php",
        );
    }

    /**
     * @param  list<string>  $middleware
     */
    private function providerContents(
        string $stub,
        string $providerClass,
        string $panelId,
        string $path,
        string $name,
        string $vite,
        array $middleware,
        bool $isDefault,
        string $shellClass,
        bool $shouldCreateShell,
    ): string {
        $defaultCall = $isDefault ? "\n            ->default()" : '';
        $shellCall = $shouldCreateShell ? "\n            ->shell({$shellClass}::class)" : '';
        $shellImport = $shouldCreateShell ? 'use '.$this->shellNamespace($panelId).'\\'.$shellClass.";\n" : '';

        return strtr($stub, [
            '{{ namespace }}' => $this->providerNamespace(),
            '{{ shell_import }}' => $shellImport,
            '{{ class }}' => $providerClass,
            '{{ id }}' => $this->quote($panelId),
            '{{ path }}' => $this->quote($path),
            '{{ name }}' => $this->quote($name),
            '{{ vite }}' => $this->quote($vite),
            '{{ middleware }}' => $this->array($middleware),
            '{{ shell }}' => $shellCall,
            '{{ default }}' => $defaultCall,
        ]);
    }

    private function shellContents(string $stub, string $panelId, string $shellClass): string
    {
        return strtr($stub, [
            '{{ namespace }}' => $this->shellNamespace($panelId),
            '{{ class }}' => $shellClass,
        ]);
    }

    private function stubPath(): string
    {
        return __DIR__.'/../../stubs/panel-provider.stub';
    }

    private function shellStubPath(): string
    {
        return __DIR__.'/../../stubs/panel-shell.stub';
    }

    private function stylesheetStubPath(): string
    {
        return __DIR__.'/../../stubs/panel.css.stub';
    }

    private function addViteInput(Filesystem $files, string $vite): void
    {
        $viteConfigPath = $this->laravel->basePath('vite.config.js');

        if (! $files->exists($viteConfigPath)) {
            $this->warn("Add [{$vite}] to your Vite inputs.");

            return;
        }

        $contents = $files->get($viteConfigPath);

        if (str_contains($contents, $this->quote($vite)) || str_contains($contents, '"'.$vite.'"')) {
            return;
        }

        $updatedContents = $this->addViteInputToArray($contents, $vite);

        if ($updatedContents === null) {
            $this->warn("Add [{$vite}] to your Vite inputs.");

            return;
        }

        $files->put($viteConfigPath, $updatedContents);
    }

    private function addViteInputToArray(string $contents, string $vite): ?string
    {
        $position = strpos($contents, 'input:');

        if ($position !== false) {
            $arrayStart = strpos($contents, '[', $position);

            if ($arrayStart === false) {
                return null;
            }

            $arrayEnd = $this->findClosingBracket($contents, $arrayStart);

            if ($arrayEnd === null) {
                return null;
            }

            return substr_replace(
                $contents,
                $this->viteInputInsertion($contents, $arrayStart, $arrayEnd, $vite),
                $arrayEnd,
                0,
            );
        }

        return null;
    }

    private function findClosingBracket(string $contents, int $arrayStart): ?int
    {
        $depth = 0;
        $length = strlen($contents);

        for ($index = $arrayStart; $index < $length; $index++) {
            if ($contents[$index] === '[') {
                $depth++;
            }

            if ($contents[$index] === ']') {
                $depth--;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function viteInputInsertion(string $contents, int $arrayStart, int $arrayEnd, string $vite): string
    {
        $arrayContents = substr($contents, $arrayStart + 1, $arrayEnd - $arrayStart - 1);

        if (! str_contains($arrayContents, "\n")) {
            return ', '.$this->quote($vite);
        }

        $lineStart = strrpos(substr($contents, 0, $arrayEnd), "\n");
        $closingIndent = $lineStart === false ? '' : substr($contents, $lineStart + 1, $arrayEnd - $lineStart - 1);
        $itemIndent = $closingIndent.'    ';

        return "{$itemIndent}{$this->quote($vite)},\n{$closingIndent}";
    }

    private function providerNamespace(): string
    {
        return rtrim($this->laravel->getNamespace(), '\\').'\\Providers';
    }

    private function shellNamespace(string $panelId): string
    {
        return rtrim($this->laravel->getNamespace(), '\\').'\\Panels\\'.$this->panelStudly($panelId);
    }

    private function panelStudly(string $panelId): string
    {
        return Str::studly($panelId);
    }

    private function quote(string $value): string
    {
        return var_export($value, true);
    }

    /**
     * @param  list<string>  $values
     */
    private function array(array $values): string
    {
        return '['.implode(', ', array_map($this->quote(...), $values)).']';
    }
}
