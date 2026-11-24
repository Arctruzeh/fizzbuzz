<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class ClassicStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'classic';
    }

    public function name(): string
    {
        return 'Classic Approach';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'The standard solution you would write in a basic interview. It checks divisibility using the modulo operator for 3, 5, and 15 (implied by the overlap). Simple, readable, and effective.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    if ($i % 15 == 0) {
        $result .= "FizzBuzz\n";
    } elseif ($i % 3 == 0) {
        $result .= "Fizz\n";
    } elseif ($i % 5 == 0) {
        $result .= "Buzz\n";
    } else {
        $result .= $i . "\n";
    }
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            if ($i % 15 == 0) {
                $result .= "FizzBuzz\n";
            } elseif ($i % 3 == 0) {
                $result .= "Fizz\n";
            } elseif ($i % 5 == 0) {
                $result .= "Buzz\n";
            } else {
                $result .= $i . "\n";
            }
        }
        return $result;
    }
}
