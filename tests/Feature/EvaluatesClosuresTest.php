<?php

declare(strict_types=1);

use Zdearo\LivewirePanels\Support\Concerns\EvaluatesClosures;

it('returns plain values unchanged', function (): void {
    $resolver = new class
    {
        use EvaluatesClosures;

        public function value(mixed $value): mixed
        {
            return $this->evaluate($value);
        }
    };

    expect($resolver->value('Admin'))->toBe('Admin');
});

it('evaluates closures with parameters', function (): void {
    $resolver = new class
    {
        use EvaluatesClosures;

        /**
         * @param  array<int, mixed>  $parameters
         */
        public function value(mixed $value, array $parameters = []): mixed
        {
            return $this->evaluate($value, $parameters);
        }
    };

    expect($resolver->value(fn (string $name): string => "Hello {$name}", ['Admin']))
        ->toBe('Hello Admin');
});
