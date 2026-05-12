<?php

declare(strict_types=1);

namespace Zdearo\LivewirePanels\Support\Concerns;

use LogicException;

trait ConfiguresPropertiesOnce
{
    protected function guardAgainstConfiguringPropertyAgain(
        bool $isConfigured,
        string $configuredValue,
        string $subject,
        string $label,
    ): void {
        if (! $isConfigured) {
            return;
        }

        throw new LogicException("The {$subject} already has the {$label} [{$configuredValue}].");
    }
}
