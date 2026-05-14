<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Tenant;

use Zdearo\LivewirePanels\Tenant\Contracts\ResolvesPanelTenant;

final class Tenant
{
    /**
     * @param  class-string  $model
     */
    public private(set) string $model;

    public private(set) ?string $routeParameter = null;

    /**
     * @var class-string<ResolvesPanelTenant>|null
     */
    public private(set) ?string $resolver = null;

    /**
     * @param  class-string  $model
     */
    public static function make(string $model): self
    {
        $tenant = new self;
        $tenant->model = $model;

        return $tenant;
    }

    public function routeParameter(string $parameter): self
    {
        $this->routeParameter = $parameter;

        return $this;
    }

    /**
     * @param  class-string<ResolvesPanelTenant>  $resolver
     */
    public function resolver(string $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }
}
