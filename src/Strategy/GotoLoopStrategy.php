<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

final class GotoLoopStrategy implements StrategyInterface
{
    public function key(): string
    {
        return 'goto_loop';
    }

    public function name(): string
    {
        return 'Goto Statement (Controversial)';
    }

    public function complexity(): string
    {
        return 'O(n)';
    }

    public function description(): string
    {
        return 'Uses PHP\'s goto for flow control. Generally considered bad practice and hard to read. Educational: demonstrates why goto is avoided in modern programming.';
    }

    public function code(): string
    {
        return <<<'CODE'
$result = '';
$i = 1;

loop_start:
if ($i > $limit) goto loop_end;

$output = '';
if ($i % 3 === 0) $output .= 'Fizz';
if ($i % 5 === 0) $output .= 'Buzz';
$result .= ($output ?: $i) . "\n";

$i++;
goto loop_start;

loop_end:
return $result;
CODE;
    }

    public function run(int $limit): string
    {
        $result = '';
        $i = 1;

        loop_start:
        if ($i > $limit) {
            goto loop_end;
        }

        $output = '';
        if ($i % 3 === 0) {
            $output .= 'Fizz';
        }
        if ($i % 5 === 0) {
            $output .= 'Buzz';
        }
        $result .= ($output ?: $i) . "\n";

        $i++;
        goto loop_start;

        loop_end:
        return $result;
    }
}
