<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class ArrayWalkStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'array_walk';
    }

    public function name(): string
    {
        return 'Range + Array Walk';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses range() and array_walk() instead of array_map(). A different functional approach that appends to an output array by reference. Interesting comparison to the array_map version.';
    }

    public function code(): string
    {
        return <<<'CODE'
$numbers = range(1, $limit);
$output = [];

array_walk($numbers, function($n) use (&$output) {
    $str = '';
    if ($n % 3 === 0) $str .= 'Fizz';
    if ($n % 5 === 0) $str .= 'Buzz';
    $output[] = ($str ?: $n) . "\n";
});

return implode('', $output);
CODE;
    }

    public function run(int $limit): string
    {
        $numbers = range(1, $limit);
        $output = [];

        array_walk($numbers, static function (int $n) use (&$output): void {
            $str = '';
            if ($n % 3 === 0) {
                $str .= 'Fizz';
            }
            if ($n % 5 === 0) {
                $str .= 'Buzz';
            }
            $output[] = ($str ?: $n) . "\n";
        });

        return implode('', $output);
    }
}
