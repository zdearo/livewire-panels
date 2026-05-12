<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function configureTemporaryPanelApp(): string
{
    $basePath = sys_get_temp_dir().'/livewire-panels-'.Str::random(16);

    File::ensureDirectoryExists($basePath.'/app/Providers');
    File::ensureDirectoryExists($basePath.'/bootstrap');

    File::put($basePath.'/bootstrap/providers.php', <<<'PHP'
<?php

return [
    App\Providers\AppServiceProvider::class,
];
PHP);

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

    return $basePath;
}

beforeEach(function (): void {
    $this->temporaryBasePath = configureTemporaryPanelApp();
});

afterEach(function (): void {
    File::deleteDirectory($this->temporaryBasePath);
});

it('creates the first panel provider and registers it as default', function (): void {
    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertSuccessful();

    $providerPath = app_path('Providers/AdminPanelProvider.php');

    expect($providerPath)
        ->toBeFile()
        ->and(File::get($providerPath))
        ->toContain('final class AdminPanelProvider extends PanelProvider')
        ->toContain("->id('admin')")
        ->toContain("->path('admin')")
        ->toContain("->name('Admin')")
        ->toContain("->vite('resources/css/panels/admin.css')")
        ->toContain("->middleware(['web'])")
        ->toContain('->default();')
        ->and(File::get(base_path('bootstrap/providers.php')))
        ->toContain('App\Providers\AdminPanelProvider::class');

    expect(resource_path('css/panels/admin.css'))
        ->toBeFile()
        ->and(File::get(resource_path('css/panels/admin.css')))
        ->toContain("@import 'tailwindcss';")
        ->toContain("@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels.css';")
        ->toContain("@source '../../../vendor/zdearo/livewire-panels/packages/panels/resources/views/**/*.blade.php';");
});

it('can ask for the panel id when it is not provided', function (): void {
    $this
        ->artisan('make:panel')
        ->expectsQuestion('What is the panel id?', 'support')
        ->assertSuccessful();

    $providerPath = app_path('Providers/SupportPanelProvider.php');

    expect($providerPath)
        ->toBeFile()
        ->and(File::get($providerPath))
        ->toContain("->id('support')")
        ->toContain("->path('support')")
        ->toContain("->name('Support')")
        ->toContain("->vite('resources/css/panels/support.css')")
        ->toContain('->default();');

    expect(resource_path('css/panels/support.css'))->toBeFile();
});

it('creates a custom panel provider without default when another panel exists', function (): void {
    File::put(app_path('Providers/AdminPanelProvider.php'), '<?php');

    $this
        ->artisan('make:panel', [
            'id' => 'customer-app',
            '--path' => 'customers',
            '--name' => 'Customers',
            '--middleware' => ['web', 'auth'],
        ])
        ->assertSuccessful();

    $providerPath = app_path('Providers/CustomerAppPanelProvider.php');

    expect($providerPath)
        ->toBeFile()
        ->and(File::get($providerPath))
        ->toContain('final class CustomerAppPanelProvider extends PanelProvider')
        ->toContain("->id('customer-app')")
        ->toContain("->path('customers')")
        ->toContain("->name('Customers')")
        ->toContain("->vite('resources/css/panels/customer-app.css')")
        ->toContain("->middleware(['web', 'auth']);")
        ->not->toContain('->default()')
        ->and(File::get(base_path('bootstrap/providers.php')))
        ->toContain('App\Providers\CustomerAppPanelProvider::class');

    expect(resource_path('css/panels/customer-app.css'))->toBeFile();
});

it('does not overwrite an existing panel provider without force', function (): void {
    $providerPath = app_path('Providers/AdminPanelProvider.php');

    File::put($providerPath, '<?php // existing');

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertFailed();

    expect(File::get($providerPath))->toBe('<?php // existing');
});

it('does not overwrite an existing panel stylesheet without force', function (): void {
    $stylesheetPath = resource_path('css/panels/admin.css');

    File::ensureDirectoryExists(dirname($stylesheetPath));
    File::put($stylesheetPath, '/* existing */');

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertFailed();

    expect(File::get($stylesheetPath))->toBe('/* existing */');
});

it('overwrites an existing panel provider with force', function (): void {
    $providerPath = app_path('Providers/AdminPanelProvider.php');

    File::put($providerPath, '<?php // existing');

    $this
        ->artisan('make:panel', [
            'id' => 'admin',
            '--force' => true,
            '--default' => true,
        ])
        ->assertSuccessful();

    expect(File::get($providerPath))
        ->toContain('final class AdminPanelProvider extends PanelProvider')
        ->toContain("->vite('resources/css/panels/admin.css')")
        ->toContain('->default();');

    expect(resource_path('css/panels/admin.css'))->toBeFile();
});
