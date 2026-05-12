<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function configureTemporaryPanelPageApp(): string
{
    $basePath = sys_get_temp_dir().'/livewire-panels-page-'.Str::random(16);

    File::ensureDirectoryExists($basePath.'/resources/views/pages');
    File::ensureDirectoryExists($basePath.'/bootstrap');

    File::put($basePath.'/composer.json', <<<'JSON'
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
JSON);

    app()->setBasePath($basePath);

    config([
        'livewire.component_namespaces.pages' => $basePath.'/resources/views/pages',
        'livewire.make_command.emoji' => false,
    ]);

    app('livewire.finder')->addNamespace('pages', $basePath.'/resources/views/pages');

    return $basePath;
}

beforeEach(function (): void {
    $this->temporaryBasePath = configureTemporaryPanelPageApp();
});

afterEach(function (): void {
    File::deleteDirectory($this->temporaryBasePath);
});

it('creates a Livewire page component and shows the panel page registration snippet', function (): void {
    $this
        ->artisan('make:panel-page', [
            'component' => 'admin.dashboard',
            '--path' => '/',
            '--name' => 'dashboard',
            '--sfc' => true,
        ])
        ->expectsOutput("Register it in your panel with: Page::make('/', 'pages::admin.dashboard')->name('dashboard')")
        ->assertSuccessful();

    expect(resource_path('views/pages/admin/dashboard.blade.php'))->toBeFile();
});

it('creates a Livewire page component using default route metadata', function (): void {
    $this
        ->artisan('make:panel-page', [
            'component' => 'pages::Reports/Index',
            '--type' => 'sfc',
            '--emoji' => 'false',
        ])
        ->expectsOutput("Register it in your panel with: Page::make('/index', 'pages::reports.index')->name('index')")
        ->assertSuccessful();

    expect(resource_path('views/pages/reports/index.blade.php'))->toBeFile();
});

it('fails when the Livewire page component already exists', function (): void {
    $this
        ->artisan('make:panel-page', [
            'component' => 'admin.dashboard',
            '--sfc' => true,
        ])
        ->assertSuccessful();

    $this
        ->artisan('make:panel-page', [
            'component' => 'admin.dashboard',
            '--sfc' => true,
        ])
        ->assertFailed();
});
