<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class StringMultStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'string_mult';
    }

    public function name(): string
    {
        return 'String Multiplication (Mathematical)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'A clever mathematical trick using str_repeat with boolean coercion. When $i % 3 == 0, the NOT operator makes it 1, repeating the string once. Compact but cryptic.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = str_repeat('Fizz', (int) !($i % 3)) . str_repeat('Buzz', (int) !($i % 5));
    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            $output = str_repeat('Fizz', (int) !($i % 3)) . str_repeat('Buzz', (int) !($i % 5));
            $result .= ($output ?: $i) . "\n";
        }
        return $result;
    }
}
