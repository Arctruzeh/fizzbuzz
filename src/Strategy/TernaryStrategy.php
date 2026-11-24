<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class TernaryStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'ternary';
    }

    public function name(): string
    {
        return 'One-Liner (Ternary)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'A "Code Golf" style solution using nested ternary operators. While concise, it can be harder to read and maintain. Good for showing off, bad for teamwork.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++)
    $result .= ($i % 15 ? ($i % 5 ? ($i % 3 ? $i : 'Fizz') : 'Buzz') : 'FizzBuzz') . "\n";
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            $result .= ($i % 15 ? ($i % 5 ? ($i % 3 ? $i : 'Fizz') : 'Buzz') : 'FizzBuzz') . "\n";
        }
        return $result;
    }
}
