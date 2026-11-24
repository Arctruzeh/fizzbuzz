<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class FastCounterStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'fast';
    }

    public function name(): string
    {
        return 'Counter Method (Educational)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Avoids modulo by using counters. Theoretically faster, but in practice modern CPUs optimize modulo so well that this is often slower due to extra variable overhead. Great for learning alternative approaches!';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
$c3 = 0;
$c5 = 0;
for ($i = 1; $i <= $limit; $i++) {
    $c3++;
    $c5++;
    $out = '';

    if ($c3 == 3) { $out = 'Fizz'; $c3 = 0; }
    if ($c5 == 5) { $out .= 'Buzz'; $c5 = 0; }

    $result .= ($out ?: $i) . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        $c3 = 0;
        $c5 = 0;
        for ($i = 1; $i <= $limit; $i++) {
            $c3++;
            $c5++;
            $out = '';

            if ($c3 == 3) {
                $out = 'Fizz';
                $c3 = 0;
            }
            if ($c5 == 5) {
                $out .= 'Buzz';
                $c5 = 0;
            }

            $result .= ($out ?: $i) . "\n";
        }
        return $result;
    }
}
