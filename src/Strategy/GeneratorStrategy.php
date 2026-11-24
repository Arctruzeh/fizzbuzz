<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

use Generator;

final class GeneratorStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'generator';
    }

    public function name(): string
    {
        return 'Generator (Lazy Evaluation)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses PHP\'s yield keyword to create a lazy iterator. Memory efficient for huge ranges since values are generated on-demand. Different paradigm worth understanding.';
    }

    public function code(): string
    {
        return <<<'CODE'
function fizzBuzzGenerator(int $limit): \Generator {
    for ($i = 1; $i <= $limit; $i++) {
        $output = '';
        if ($i % 3 === 0) $output .= 'Fizz';
        if ($i % 5 === 0) $output .= 'Buzz';
        yield ($output ?: $i) . "\n";
    }
}

$result = '';
foreach (fizzBuzzGenerator($limit) as $line) {
    $result .= $line;
}
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        foreach ($this->generate($limit) as $line) {
            $result .= $line;
        }
        return $result;
    }

    private function generate(int $limit): Generator
    {
        for ($i = 1; $i <= $limit; $i++) {
            $output = '';
            if ($i % 3 === 0) {
                $output .= 'Fizz';
            }
            if ($i % 5 === 0) {
                $output .= 'Buzz';
            }
            yield ($output ?: $i) . "\n";
        }
    }
}
