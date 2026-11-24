<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class CleanStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'clean';
    }

    public function name(): string
    {
        return 'Concatenation (Cleaner)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'A slightly more elegant approach that avoids the explicit "15" check by building the string incrementally. This scales better if you need to add "7 -> Bazz" later.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = '';
    if ($i % 3 === 0) $output .= 'Fizz';
    if ($i % 5 === 0) $output .= 'Buzz';

    // If output is empty, it's just the number
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
            if ($i % 3 === 0) {
                $output .= 'Fizz';
            }
            if ($i % 5 === 0) {
                $output .= 'Buzz';
            }

            $result .= ($output ?: $i) . "\n";
        }
        return $result;
    }
}
