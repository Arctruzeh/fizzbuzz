<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class RecursiveStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'recursive';
    }

    public function name(): string
    {
        return 'Recursive';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses recursion instead of loops. Demonstrates functional programming concepts but has function call overhead. Stack depth limited, so not practical for large N.';
    }

    public function code(): string
    {
        return <<<'CODE'
function fizzBuzzRecursive(int $n, int $limit, string &$result): void {
    if ($n > $limit) return;

    $output = '';
    if ($n % 3 === 0) $output .= 'Fizz';
    if ($n % 5 === 0) $output .= 'Buzz';
    $result .= ($output ?: $n) . "\n";

    fizzBuzzRecursive($n + 1, $limit, $result);
}

$result = '';
fizzBuzzRecursive(1, $limit, $result);
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        $this->step(1, $limit, $result);
        return $result;
    }

    private function step(int $n, int $limit, string &$result): void
    {
        if ($n > $limit) {
            return;
        }

        $output = '';
        if ($n % 3 === 0) {
            $output .= 'Fizz';
        }
        if ($n % 5 === 0) {
            $output .= 'Buzz';
        }
        $result .= ($output ?: $n) . "\n";

        $this->step($n + 1, $limit, $result);
    }
}
