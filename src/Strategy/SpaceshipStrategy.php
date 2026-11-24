<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class SpaceshipStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'spaceship';
    }

    public function name(): string
    {
        return 'Spaceship Operator (PHP 7+)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses PHP 7\'s spaceship operator (<=>) creatively. More of a novelty than practical. Shows modern PHP features in an unconventional way.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $fizz = ($i % 3 <=> 0) === 0;
    $buzz = ($i % 5 <=> 0) === 0;

    $output = '';
    if ($fizz) $output .= 'Fizz';
    if ($buzz) $output .= 'Buzz';

    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($i = 1; $i <= $limit; $i++) {
            $fizz = ($i % 3 <=> 0) === 0;
            $buzz = ($i % 5 <=> 0) === 0;

            $output = '';
            if ($fizz) {
                $output .= 'Fizz';
            }
            if ($buzz) {
                $output .= 'Buzz';
            }

            $result .= ($output ?: $i) . "\n";
        }
        return $result;
    }
}
