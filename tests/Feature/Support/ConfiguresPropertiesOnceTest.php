<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Support\Concerns\ConfiguresPropertiesOnce;

it('prevents a configuration property from being assigned twice', function (): void {
    $configurable = new class
    {
        use ConfiguresPropertiesOnce;

        public string $id;

        public function id(string $id): self
        {
            $this->guardAgainstConfiguringPropertyAgain(
                isset($this->id),
                $this->id ?? '',
                'panel',
                'ID',
            );

            $this->id = $id;

            return $this;
        }
    };

    $configurable->id('admin');

    expect(fn () => $configurable->id('app'))
        ->toThrow(LogicException::class, 'The panel already has the ID [admin].');
});
