<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class OriginalStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'original';
    }

    public function name(): string
    {
        return 'Original (Portfolio Version)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'The version deployed to ajlato.com. Clean, concatenation-based approach that builds the output string incrementally. Professional and interview-ready.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($x = 1; $x <= $limit; $x++) {
    $y = '';
    if ($x % 3 == 0) {
        $y .= 'Fizz';
    }
    if ($x % 5 == 0) {
        $y .= 'Buzz';
    }
    if ($y == '') {
        $y = $x;
    }
    $result .= $y . "\n";
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        for ($x = 1; $x <= $limit; $x++) {
            $y = '';
            if ($x % 3 == 0) {
                $y .= 'Fizz';
            }
            if ($x % 5 == 0) {
                $y .= 'Buzz';
            }
            if ($y == '') {
                $y = $x;
            }
            $result .= $y . "\n";
        }
        return $result;
    }
}
