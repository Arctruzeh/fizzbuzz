<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class MatchStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'match';
    }

    public function name(): string
    {
        return 'Match Expression (PHP 8+)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses PHP 8\'s match expression, which is stricter and more concise than switch. Returns a value directly without break statements. Modern and clean.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $result .= match ($i % 15) {
        0 => 'FizzBuzz',
        3, 6, 9, 12 => 'Fizz',
        5, 10 => 'Buzz',
        default => $i
    } . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            $result .= match ($i % 15) {
                0 => 'FizzBuzz',
                3, 6, 9, 12 => 'Fizz',
                5, 10 => 'Buzz',
                default => $i,
            } . "\n";
        }
        return $result;
    }
}
