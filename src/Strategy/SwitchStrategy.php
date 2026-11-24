<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class SwitchStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'switch';
    }

    public function name(): string
    {
        return 'Switch Statement';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses a switch statement on $i % 15 to handle all cases. Shows an alternative control structure to if/else. Generally slower due to jump table overhead.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    switch ($i % 15) {
        case 0:
            $result .= "FizzBuzz\n";
            break;
        case 3:
        case 6:
        case 9:
        case 12:
            $result .= "Fizz\n";
            break;
        case 5:
        case 10:
            $result .= "Buzz\n";
            break;
        default:
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
            switch ($i % 15) {
                case 0:
                    $result .= "FizzBuzz\n";
                    break;
                case 3:
                case 6:
                case 9:
                case 12:
                    $result .= "Fizz\n";
                    break;
                case 5:
                case 10:
                    $result .= "Buzz\n";
                    break;
                default:
                    $result .= $i . "\n";
            }
        }
        return $result;
    }
}
