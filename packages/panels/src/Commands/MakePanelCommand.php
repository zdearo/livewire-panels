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
        {--force : Overwrite the panel provider if it already exists}';

    protected $description = 'Create a new Livewire panel provider';

    public function handle(Filesystem $files): int
    {
        $panelId = $this->panelId();
        $providerClass = $this->providerClass($panelId);
        $providerPath = $this->laravel->basePath("app/Providers/{$providerClass}.php");

        if ($files->exists($providerPath) && ! $this->option('force')) {
            $this->error("Panel provider [{$providerClass}] already exists.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($providerPath));

        $files->put(
            $providerPath,
            $this->providerContents(
                stub: $files->get($this->stubPath()),
                providerClass: $providerClass,
                panelId: $panelId,
                path: $this->panelPath($panelId),
                name: $this->panelName($panelId),
                middleware: $this->middleware(),
                isDefault: $this->isDefaultPanel($providerClass),
            ),
        );

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
        array $middleware,
        bool $isDefault,
    ): string {
        $defaultCall = $isDefault ? "\n            ->default()" : '';

        return strtr($stub, [
            '{{ namespace }}' => $this->providerNamespace(),
            '{{ class }}' => $providerClass,
            '{{ id }}' => $this->quote($panelId),
            '{{ path }}' => $this->quote($path),
            '{{ name }}' => $this->quote($name),
            '{{ middleware }}' => $this->array($middleware),
            '{{ default }}' => $defaultCall,
        ]);
    }

    private function stubPath(): string
    {
        return __DIR__.'/../../stubs/panel-provider.stub';
    }

    private function providerNamespace(): string
    {
        return rtrim($this->laravel->getNamespace(), '\\').'\\Providers';
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
