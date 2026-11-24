<?php

declare(strict_types=1);

namespace FizzBuzz;

/**
 * FizzBuzz Strategy Definitions
 *
 * Contains all FizzBuzz algorithm implementations used in the benchmark application.
 */
class Strategies
{
    public const ALLOWED_LIMITS = [100, 500, 1000, 2000];
    public const DEFAULT_LIMIT = 1000;

    /** @var array<int, string> */
    private static array $precomputed = [];

    private static function fizzBuzzGenerator(int $limit): \Generator
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

    private static function fizzBuzzRecursive(int $n, int $limit, string &$result): void
    {
        if ($n > $limit) {
            return;
        }

        $output = '';
        if ($n % 3 === 0) {
            $output .= 'Fizz';
        }
        if ($n % 5 === 0) {
            $output .= 'Buzz';
        }
        $result .= ($output ?: $n) . "\n";

        self::fizzBuzzRecursive($n + 1, $limit, $result);
    }

    /**
     * Get all available strategies (including executable 'run' callables)
     *
     * @return array<string, array{name: string, complexity: string, description: string, code: string, run: callable(int): string}>
     */
    public static function getAll(): array
    {
        return [
            'original' => [
                'name' => 'Original (Portfolio Version)',
                'complexity' => 'O(n)',
                'description' => 'The version deployed to ajlato.com. Clean, concatenation-based approach that builds the output string incrementally. Professional and interview-ready.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'classic' => [
                'name' => 'Classic Approach',
                'complexity' => 'O(n)',
                'description' => 'The standard solution you would write in a basic interview. It checks divisibility using the modulo operator for 3, 5, and 15 (implied by the overlap). Simple, readable, and effective.',
                'code' => <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    if ($i % 15 == 0) {
        $result .= "FizzBuzz\n";
    } elseif ($i % 3 == 0) {
        $result .= "Fizz\n";
    } elseif ($i % 5 == 0) {
        $result .= "Buzz\n";
    } else {
        $result .= $i . "\n";
    }
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    for ($i = 1; $i <= $limit; $i++) {
                        if ($i % 15 == 0) {
                            $result .= "FizzBuzz\n";
                        } elseif ($i % 3 == 0) {
                            $result .= "Fizz\n";
                        } elseif ($i % 5 == 0) {
                            $result .= "Buzz\n";
                        } else {
                            $result .= $i . "\n";
                        }
                    }
                    return $result;
                },
            ],
            'clean' => [
                'name' => 'Concatenation (Cleaner)',
                'complexity' => 'O(n)',
                'description' => 'A slightly more elegant approach that avoids the explicit "15" check by building the string incrementally. This scales better if you need to add "7 -> Bazz" later.',
                'code' => <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = '';
    if ($i % 3 === 0) $output .= 'Fizz';
    if ($i % 5 === 0) $output .= 'Buzz';

    // If output is empty, it's just the number
    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    for ($i = 1; $i <= $limit; $i++) {
                        $output = '';
                        if ($i % 3 === 0) {
                            $output .= 'Fizz';
                        }
                        if ($i % 5 === 0) {
                            $output .= 'Buzz';
                        }

                        $result .= ($output ?: $i) . "\n";
                    }
                    return $result;
                },
            ],
            'fast' => [
                'name' => 'Counter Method (Educational)',
                'complexity' => 'O(n)',
                'description' => 'Avoids modulo by using counters. Theoretically faster, but in practice modern CPUs optimize modulo so well that this is often slower due to extra variable overhead. Great for learning alternative approaches!',
                'code' => <<<'CODE'
$result = '';
$c3 = 0;
$c5 = 0;
for ($i = 1; $i <= $limit; $i++) {
    $c3++;
    $c5++;
    $out = '';

    if ($c3 == 3) { $out = 'Fizz'; $c3 = 0; }
    if ($c5 == 5) { $out .= 'Buzz'; $c5 = 0; }

    $result .= ($out ?: $i) . "\n";
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    $c3 = 0;
                    $c5 = 0;
                    for ($i = 1; $i <= $limit; $i++) {
                        $c3++;
                        $c5++;
                        $out = '';

                        if ($c3 == 3) {
                            $out = 'Fizz';
                            $c3 = 0;
                        }
                        if ($c5 == 5) {
                            $out .= 'Buzz';
                            $c5 = 0;
                        }

                        $result .= ($out ?: $i) . "\n";
                    }
                    return $result;
                },
            ],
            'ternary' => [
                'name' => 'One-Liner (Ternary)',
                'complexity' => 'O(n)',
                'description' => 'A "Code Golf" style solution using nested ternary operators. While concise, it can be harder to read and maintain. Good for showing off, bad for teamwork.',
                'code' => <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++)
    $result .= ($i % 15 ? ($i % 5 ? ($i % 3 ? $i : 'Fizz') : 'Buzz') : 'FizzBuzz') . "\n";
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    for ($i = 1; $i <= $limit; $i++) {
                        $result .= ($i % 15 ? ($i % 5 ? ($i % 3 ? $i : 'Fizz') : 'Buzz') : 'FizzBuzz') . "\n";
                    }
                    return $result;
                },
            ],
            'functional' => [
                'name' => 'Functional (Array Map)',
                'complexity' => 'O(n)',
                'description' => 'A functional programming approach using array_map. It separates the generation of the range from the processing logic. Uses more memory since it builds the entire array before output.',
                'code' => <<<'CODE'
$output = array_map(function($n) {
    $str = '';
    $str .= ($n % 3 === 0) ? 'Fizz' : '';
    $str .= ($n % 5 === 0) ? 'Buzz' : '';
    return ($str ?: $n) . "\n";
}, range(1, $limit));

return implode('', $output);
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $output = array_map(static function (int $n): string {
                        $str = '';
                        $str .= ($n % 3 === 0) ? 'Fizz' : '';
                        $str .= ($n % 5 === 0) ? 'Buzz' : '';
                        return ($str ?: $n) . "\n";
                    }, range(1, $limit));

                    return implode('', $output);
                },
            ],
            'lookup' => [
                'name' => 'Lookup Table (Pattern-Based)',
                'complexity' => 'O(n)',
                'description' => 'Exploits the fact that FizzBuzz repeats every 15 numbers. Pre-computes the pattern and uses modulo 15 to index it. Minimizes conditionals for potential performance gains.',
                'code' => <<<'CODE'
$pattern = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'FizzBuzz'];
$result = '';

for ($i = 1; $i <= $limit; $i++) {
    $index = ($i - 1) % 15;
    $value = $pattern[$index];

    // Replace numeric placeholders with actual number
    if (is_int($value)) {
        $result .= $i . "\n";
    } else {
        $result .= $value . "\n";
    }
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $pattern = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'FizzBuzz'];
                    $result = '';

                    for ($i = 1; $i <= $limit; $i++) {
                        $index = ($i - 1) % 15;
                        $value = $pattern[$index];

                        if (is_int($value)) {
                            $result .= $i . "\n";
                        } else {
                            $result .= $value . "\n";
                        }
                    }
                    return $result;
                },
            ],
            'string_mult' => [
                'name' => 'String Multiplication (Mathematical)',
                'complexity' => 'O(n)',
                'description' => 'A clever mathematical trick using str_repeat with boolean coercion. When $i % 3 == 0, the NOT operator makes it 1, repeating the string once. Compact but cryptic.',
                'code' => <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = str_repeat('Fizz', (int) !($i % 3)) . str_repeat('Buzz', (int) !($i % 5));
    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    for ($i = 1; $i <= $limit; $i++) {
                        $output = str_repeat('Fizz', (int) !($i % 3)) . str_repeat('Buzz', (int) !($i % 5));
                        $result .= ($output ?: $i) . "\n";
                    }
                    return $result;
                },
            ],
            'switch' => [
                'name' => 'Switch Statement',
                'complexity' => 'O(n)',
                'description' => 'Uses a switch statement on $i % 15 to handle all cases. Shows an alternative control structure to if/else. Generally slower due to jump table overhead.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'match' => [
                'name' => 'Match Expression (PHP 8+)',
                'complexity' => 'O(n)',
                'description' => 'Uses PHP 8\'s match expression, which is stricter and more concise than switch. Returns a value directly without break statements. Modern and clean.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'generator' => [
                'name' => 'Generator (Lazy Evaluation)',
                'complexity' => 'O(n)',
                'description' => 'Uses PHP\'s yield keyword to create a lazy iterator. Memory efficient for huge ranges since values are generated on-demand. Different paradigm worth understanding.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    foreach (self::fizzBuzzGenerator($limit) as $line) {
                        $result .= $line;
                    }
                    return $result;
                },
            ],
            'recursive' => [
                'name' => 'Recursive',
                'complexity' => 'O(n)',
                'description' => 'Uses recursion instead of loops. Demonstrates functional programming concepts but has function call overhead. Stack depth limited, so not practical for large N.',
                'code' => <<<'CODE'
function fizzBuzzRecursive(int $n, int $limit, string &$result): void {
    if ($n > $limit) return;

    $output = '';
    if ($n % 3 === 0) $output .= 'Fizz';
    if ($n % 5 === 0) $output .= 'Buzz';
    $result .= ($output ?: $n) . "\n";

    fizzBuzzRecursive($n + 1, $limit, $result);
}

$result = '';
fizzBuzzRecursive(1, $limit, $result);
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    self::fizzBuzzRecursive(1, $limit, $result);
                    return $result;
                },
            ],
            'precomputed' => [
                'name' => 'Pre-computed String (Ultimate Speed)',
                'complexity' => 'O(1)',
                'description' => 'The "ultimate" optimization - pre-generate the output once per limit and return the cached string. No loop during timed runs, just returning a string reference. Educational extreme.',
                'code' => <<<'CODE'
if (!isset($precomputed[$limit])) {
    $lines = [];
    for ($i = 1; $i <= $limit; $i++) {
        $out = '';
        if ($i % 3 === 0) $out .= 'Fizz';
        if ($i % 5 === 0) $out .= 'Buzz';
        $lines[] = ($out ?: $i) . "\n";
    }
    $precomputed[$limit] = implode('', $lines);
}

return $precomputed[$limit];
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    if (!isset(self::$precomputed[$limit])) {
                        $lines = [];
                        for ($i = 1; $i <= $limit; $i++) {
                            $out = '';
                            if ($i % 3 === 0) {
                                $out .= 'Fizz';
                            }
                            if ($i % 5 === 0) {
                                $out .= 'Buzz';
                            }
                            $lines[] = ($out ?: $i) . "\n";
                        }
                        self::$precomputed[$limit] = implode('', $lines);
                    }

                    return self::$precomputed[$limit];
                },
            ],
            'goto_loop' => [
                'name' => 'Goto Statement (Controversial)',
                'complexity' => 'O(n)',
                'description' => 'Uses PHP\'s goto for flow control. Generally considered bad practice and hard to read. Educational: demonstrates why goto is avoided in modern programming.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'spaceship' => [
                'name' => 'Spaceship Operator (PHP 7+)',
                'complexity' => 'O(n)',
                'description' => 'Uses PHP 7\'s spaceship operator (<=>) creatively. More of a novelty than practical. Shows modern PHP features in an unconventional way.',
                'code' => <<<'CODE'
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
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'array_walk' => [
                'name' => 'Range + Array Walk',
                'complexity' => 'O(n)',
                'description' => 'Uses range() and array_walk() instead of array_map(). A different functional approach that appends to an output array by reference. Interesting comparison to the array_map version.',
                'code' => <<<'CODE'
$numbers = range(1, $limit);
$output = [];

array_walk($numbers, function($n) use (&$output) {
    $str = '';
    if ($n % 3 === 0) $str .= 'Fizz';
    if ($n % 5 === 0) $str .= 'Buzz';
    $output[] = ($str ?: $n) . "\n";
});

return implode('', $output);
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
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
                },
            ],
            'math_division' => [
                'name' => 'Modulo-Free (Division)',
                'complexity' => 'O(n)',
                'description' => 'Avoids modulo by using division and floor comparison. Tests if floor($i/3) changes between consecutive numbers. Theoretical alternative but slower due to floating-point operations.',
                'code' => <<<'CODE'
$result = '';
for ($i = 1; $i <= $limit; $i++) {
    $output = '';

    // Check if we crossed a multiple of 3
    if (floor($i / 3) != floor(($i - 1) / 3)) {
        $output .= 'Fizz';
    }

    // Check if we crossed a multiple of 5
    if (floor($i / 5) != floor(($i - 1) / 5)) {
        $output .= 'Buzz';
    }

    $result .= ($output ?: $i) . "\n";
}
return $result;
CODE,
                'run' => static function (int $limit = self::DEFAULT_LIMIT): string {
                    $result = '';
                    for ($i = 1; $i <= $limit; $i++) {
                        $output = '';

                        if (floor($i / 3) != floor(($i - 1) / 3)) {
                            $output .= 'Fizz';
                        }

                        if (floor($i / 5) != floor(($i - 1) / 5)) {
                            $output .= 'Buzz';
                        }

                        $result .= ($output ?: $i) . "\n";
                    }
                    return $result;
                },
            ],
        ];
    }

    /**
     * Get strategy metadata and source code without executable closures for frontend rendering
     *
     * @return array<string, array{name: string, complexity: string, description: string, code: string}>
     */
    public static function getPublicData(): array
    {
        return array_map(static function (array $strategy): array {
            unset($strategy['run']);
            return $strategy;
        }, self::getAll());
    }
}
