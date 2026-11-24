<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class FunctionalStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'functional';
    }

    public function name(): string
    {
        return 'Functional (Array Map)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'A functional programming approach using array_map. It separates the generation of the range from the processing logic. Uses more memory since it builds the entire array before output.';
    }

    public function code(): string
    {
        return <<<'CODE'
$output = array_map(function($n) {
    $str = '';
    $str .= ($n % 3 === 0) ? 'Fizz' : '';
    $str .= ($n % 5 === 0) ? 'Buzz' : '';
    return ($str ?: $n) . "\n";
}, range(1, $limit));

return implode('', $output);
CODE;
    }

    public function run(int $limit): string
    {
        $output = array_map(static function (int $n): string {
            $str = '';
            $str .= ($n % 3 === 0) ? 'Fizz' : '';
            $str .= ($n % 5 === 0) ? 'Buzz' : '';
            return ($str ?: $n) . "\n";
        }, range(1, $limit));

        return implode('', $output);
    }
}
