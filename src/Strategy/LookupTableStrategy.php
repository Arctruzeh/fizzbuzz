<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class LookupTableStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'lookup';
    }

    public function name(): string
    {
        return 'Lookup Table (Pattern-Based)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Exploits the fact that FizzBuzz repeats every 15 numbers. Pre-computes the pattern and uses modulo 15 to index it. Minimizes conditionals for potential performance gains.';
    }

    public function code(): string
    {
        return <<<'CODE'
$pattern = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'FizzBuzz'];
$result = '';

for ($i = 1; $i <= $limit; $i++) {
    $index = ($i - 1) % 15;
    $value = $pattern[$index];

    // Replace numeric placeholders with actual number
    if (is_int($value)) {
        $result .= $i . "\n";
    } else {
        $result .= $value . "\n";
    }
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $pattern = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'FizzBuzz'];
        $result = '';

        for ($i = 1; $i <= $limit; $i++) {
            $index = ($i - 1) % 15;
            $value = $pattern[$index];

            if (is_int($value)) {
                $result .= $i . "\n";
            } else {
                $result .= $value . "\n";
            }
        }
        return $result;
    }
}
