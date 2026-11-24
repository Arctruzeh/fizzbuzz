<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class PrecomputedStrategy implements StrategyInterface
{
    /** @var array<int, string> */
    private array $cache = [];

    public function key(): string
    {
        return 'precomputed';
    }

    public function name(): string
    {
        return 'Pre-computed String (Ultimate Speed)';
    }

    public function complexity(): string
    {
        return 'O(1)';
    }

    public function description(): string
    {
        return 'The "ultimate" optimization - pre-generate the output once per limit and return the cached string. No loop during timed runs, just returning a string reference. Educational extreme.';
    }

    public function code(): string
    {
        return <<<'CODE'
if (!isset($precomputed[$limit])) {
    $lines = [];
    for ($i = 1; $i <= $limit; $i++) {
        $out = '';
        if ($i % 3 === 0) $out .= 'Fizz';
        if ($i % 5 === 0) $out .= 'Buzz';
        $lines[] = ($out ?: $i) . "\n";
    }
    $precomputed[$limit] = implode('', $lines);
}

return $precomputed[$limit];
CODE;
    }

    public function run(int $limit): string
    {
        if (!isset($this->cache[$limit])) {
            $lines = [];
            for ($i = 1; $i <= $limit; $i++) {
                $out = '';
                if ($i % 3 === 0) {
                    $out .= 'Fizz';
                }
                if ($i % 5 === 0) {
                    $out .= 'Buzz';
                }
                $lines[] = ($out ?: $i) . "\n";
            }
            $this->cache[$limit] = implode('', $lines);
        }

        return $this->cache[$limit];
    }
}
