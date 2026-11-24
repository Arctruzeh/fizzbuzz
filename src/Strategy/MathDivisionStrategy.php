<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class MathDivisionStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'math_division';
    }

    public function name(): string
    {
        return 'Modulo-Free (Division)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Avoids modulo by using division and floor comparison. Tests if floor($i/3) changes between consecutive numbers. Theoretical alternative but slower due to floating-point operations.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = '';

    // Check if we crossed a multiple of 3
    if (floor($i / 3) != floor(($i - 1) / 3)) {
        $output .= 'Fizz';
    }

    // Check if we crossed a multiple of 5
    if (floor($i / 5) != floor(($i - 1) / 5)) {
        $output .= 'Buzz';
    }

    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            $output = '';

            if (floor($i / 3) != floor(($i - 1) / 3)) {
                $output .= 'Fizz';
            }

            if (floor($i / 5) != floor(($i - 1) / 5)) {
                $output .= 'Buzz';
            }

            $result .= ($output ?: $i) . "\n";
        }
        return $result;
    }
}
