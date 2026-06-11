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

    File::put($basePath.'/vite.config.js', <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
JS);

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
        ->toContain("@import '../../../vendor/livewire/flux/dist/flux.css';")
        ->toContain("@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels.css';")
        ->toContain("@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels-theme.css';")
        ->toContain("@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels.css';\n@import '../../../vendor/zdearo/livewire-panels/packages/panels/resources/css/panels-theme.css';")
        ->toContain("@source '../../../vendor/zdearo/livewire-panels/packages/panels/resources/views/**/*.blade.php';");

    expect(File::get(base_path('vite.config.js')))
        ->toContain("'resources/css/panels/admin.css'")
        ->toContain("'resources/css/app.css',");
});

it('can ask for the panel id when it is not provided', function (): void {
    $this
        ->artisan('make:panel')
        ->expectsQuestion('What is the panel id?', 'support')
        ->expectsConfirmation('Create a custom panel shell class?', 'no')
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

    expect(File::get(base_path('vite.config.js')))
        ->toContain("'resources/css/panels/support.css'");
});

it('can create a custom panel shell class', function (): void {
    $this
        ->artisan('make:panel', [
            'id' => 'admin',
            '--shell' => true,
        ])
        ->assertSuccessful();

    $providerPath = app_path('Providers/AdminPanelProvider.php');
    $shellPath = app_path('Panels/Admin/AdminPanelShell.php');

    expect($providerPath)
        ->toBeFile()
        ->and(File::get($providerPath))
        ->toContain('use App\Panels\Admin\AdminPanelShell;')
        ->toContain('->shell(AdminPanelShell::class)')
        ->and($shellPath)
        ->toBeFile()
        ->and(File::get($shellPath))
        ->toContain('namespace App\Panels\Admin;')
        ->toContain('final class AdminPanelShell extends DefaultPanelShell')
        ->toContain('public function sidebarBrand(Panel $panel): View');
});

it('can ask whether to create a custom panel shell class', function (): void {
    $this
        ->artisan('make:panel')
        ->expectsQuestion('What is the panel id?', 'admin')
        ->expectsConfirmation('Create a custom panel shell class?', 'yes')
        ->assertSuccessful();

    expect(app_path('Panels/Admin/AdminPanelShell.php'))->toBeFile()
        ->and(File::get(app_path('Providers/AdminPanelProvider.php')))
        ->toContain('->shell(AdminPanelShell::class)');
});

it('does not overwrite an existing panel shell without force', function (): void {
    $shellPath = app_path('Panels/Admin/AdminPanelShell.php');

    File::ensureDirectoryExists(dirname($shellPath));
    File::put($shellPath, '<?php // existing');

    $this
        ->artisan('make:panel', [
            'id' => 'admin',
            '--shell' => true,
        ])
        ->assertFailed();

    expect(File::get($shellPath))->toBe('<?php // existing');
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

    expect(File::get(base_path('vite.config.js')))
        ->toContain("'resources/css/panels/customer-app.css'");
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

it('does not duplicate an existing panel stylesheet Vite input', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/panels/admin.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertSuccessful();

    expect(substr_count(File::get(base_path('vite.config.js')), "'resources/css/panels/admin.css'"))
        ->toBe(1);
});

it('does not duplicate an existing double quoted panel stylesheet Vite input', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/panels/admin.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertSuccessful();

    expect(substr_count(File::get(base_path('vite.config.js')), '"resources/css/panels/admin.css"'))
        ->toBe(1);
});

it('adds the panel stylesheet to a multiline Vite input array', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->assertSuccessful();

    expect(File::get(base_path('vite.config.js')))
        ->toContain("                'resources/css/panels/admin.css',");
});

it('warns when the Vite config file is missing', function (): void {
    File::delete(base_path('vite.config.js'));

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->expectsOutputToContain('Add [resources/css/panels/admin.css] to your Vite inputs.')
        ->assertSuccessful();

    expect(base_path('vite.config.js'))->not->toBeFile();
});

it('warns when the Vite config cannot be updated automatically', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
export default {};
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->expectsOutputToContain('Add [resources/css/panels/admin.css] to your Vite inputs.')
        ->assertSuccessful();

    expect(File::get(base_path('vite.config.js')))->toBe('export default {};');
});

it('warns when the Vite input option is not an array', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
export default {
    input: 'resources/css/app.css',
};
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->expectsOutputToContain('Add [resources/css/panels/admin.css] to your Vite inputs.')
        ->assertSuccessful();

    expect(File::get(base_path('vite.config.js')))->toBe(<<<'JS'
export default {
    input: 'resources/css/app.css',
};
JS);
});

it('warns when the Vite input array is not closed', function (): void {
    File::put(base_path('vite.config.js'), <<<'JS'
export default {
    input: ['resources/css/app.css',
};
JS);

    $this
        ->artisan('make:panel', ['id' => 'admin'])
        ->expectsOutputToContain('Add [resources/css/panels/admin.css] to your Vite inputs.')
        ->assertSuccessful();

    expect(File::get(base_path('vite.config.js')))->toBe(<<<'JS'
export default {
    input: ['resources/css/app.css',
};
JS);
});
