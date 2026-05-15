<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tests;

use Closure;
use Flux\FluxServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Zdearo\LivewirePanels\LivewirePanelsServiceProvider;

abstract class TestCase extends Orchestra
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        Blade::component(TestingBladeIcon::class, 'icon');
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FluxServiceProvider::class,
            LivewireServiceProvider::class,
            LivewirePanelsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }
}

final class TestingBladeIcon extends Component
{
    public function __construct(
        public string $name,
    ) {}

    public function render(): Closure
    {
        return fn (array $data): string => '<svg '.$data['attributes']
            ->merge(['data-blade-icon' => $this->name])
            ->toHtml().'></svg>';
    }
}
