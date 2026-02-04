<?php
/**
 * FizzBuzz Strategy Definitions
 * 
 * This file contains all the different FizzBuzz algorithm implementations
 * used in the benchmark application.
 */

$strategies = [
    'original' => [
        'name' => 'Original (Portfolio Version)',
        'complexity' => 'O(n)',
        'description' => 'The version deployed to ajlato.com. Clean, concatenation-based approach that builds the output string incrementally. Professional and interview-ready.',
        'code' => <<<'CODE'
for ($x = 1; $x <= 10000; $x++) {
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
    echo $y . '<br>';
}
CODE
    ],
    'classic' => [
        'name' => 'Classic Approach',
        'complexity' => 'O(n)',
        'description' => 'The standard solution you would write in a basic interview. It checks divisibility using the modulo operator for 3, 5, and 15 (implied by the overlap). Simple, readable, and effective.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    if ($i % 15 == 0) {
        echo "FizzBuzz<br>";
    } elseif ($i % 3 == 0) {
        echo "Fizz<br>";
    } elseif ($i % 5 == 0) {
        echo "Buzz<br>";
    } else {
        echo $i . "<br>";
    }
}
CODE
    ],
    'clean' => [
        'name' => 'Concatenation (Cleaner)',
        'complexity' => 'O(n)',
        'description' => 'A slightly more elegant approach that avoids the explicit "15" check by building the string incrementally. This scales better if you need to add "7 -> Bazz" later.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    $output = '';
    if ($i % 3 === 0) $output .= 'Fizz';
    if ($i % 5 === 0) $output .= 'Buzz';
    
    // If output is empty, it's just the number
    echo ($output ?: $i) . "<br>";
}
CODE
    ],
    'fast' => [
        'name' => 'Counter Method (Educational)',
        'complexity' => 'O(n)',
        'description' => 'Avoids modulo by using counters. Theoretically faster, but in practice modern CPUs optimize modulo so well that this is often slower due to extra variable overhead. Great for learning alternative approaches!',
        'code' => <<<'CODE'
$c3 = 0;
$c5 = 0;
for ($i = 1; $i <= 10000; $i++) {
    $c3++; 
    $c5++;
    $out = '';
    
    if ($c3 == 3) { $out = 'Fizz'; $c3 = 0; }
    if ($c5 == 5) { $out .= 'Buzz'; $c5 = 0; }
    
    echo ($out ?: $i) . "<br>";
}
CODE
    ],
    'ternary' => [
        'name' => 'One-Liner (Ternary)',
        'complexity' => 'O(n)',
        'description' => 'A "Code Golf" style solution using nested ternary operators. While concise, it can be harder to read and maintain. Good for showing off, bad for teamwork.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) 
    echo ($i % 15 ? ($i % 5 ? ($i % 3 ? $i : 'Fizz') : 'Buzz') : 'FizzBuzz') . "<br>";
CODE
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
    return ($str ?: $n) . "<br>";
}, range(1, 10000));

echo implode('', $output);
CODE
    ],
    'lookup' => [
        'name' => 'Lookup Table (Pattern-Based)',
        'complexity' => 'O(n)',
        'description' => 'Exploits the fact that FizzBuzz repeats every 15 numbers. Pre-computes the pattern and uses modulo 15 to index it. Minimizes conditionals for potential performance gains.',
        'code' => <<<'CODE'
$pattern = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'FizzBuzz'];

for ($i = 1; $i <= 10000; $i++) {
    $index = (($i - 1) % 15);
    $value = $pattern[$index];
    
    // Replace numeric placeholders with actual number
    if (is_int($value)) {
        echo $i . "<br>";
    } else {
        echo $value . "<br>";
    }
}
CODE
    ],
    'string_mult' => [
        'name' => 'String Multiplication (Mathematical)',
        'complexity' => 'O(n)',
        'description' => 'A clever mathematical trick using str_repeat with boolean coercion. When $i % 3 == 0, the NOT operator makes it 1, repeating the string once. Compact but cryptic.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    $output = str_repeat('Fizz', !($i % 3)) . str_repeat('Buzz', !($i % 5));
    echo ($output ?: $i) . "<br>";
}
CODE
    ],
    'switch' => [
        'name' => 'Switch Statement',
        'complexity' => 'O(n)',
        'description' => 'Uses a switch statement on $i % 15 to handle all cases. Shows an alternative control structure to if/else. Generally slower due to jump table overhead.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    switch ($i % 15) {
        case 0:
            echo "FizzBuzz<br>";
            break;
        case 3:
        case 6:
        case 9:
        case 12:
            echo "Fizz<br>";
            break;
        case 5:
        case 10:
            echo "Buzz<br>";
            break;
        default:
            echo $i . "<br>";
    }
}
CODE
    ],
    'match' => [
        'name' => 'Match Expression (PHP 8+)',
        'complexity' => 'O(n)',
        'description' => 'Uses PHP 8\'s new match expression, which is stricter and more concise than switch. Returns a value directly without break statements. Modern and clean.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    echo match ($i % 15) {
        0 => 'FizzBuzz',
        3, 6, 9, 12 => 'Fizz',
        5, 10 => 'Buzz',
        default => $i
    } . "<br>";
}
CODE
    ],
    'generator' => [
        'name' => 'Generator (Lazy Evaluation)',
        'complexity' => 'O(n)',
        'description' => 'Uses PHP\'s yield keyword to create a lazy iterator. Memory efficient for huge ranges since values are generated on-demand. Different paradigm worth understanding.',
        'code' => <<<'CODE'
if (!function_exists('fizzBuzzGenerator')) {
    function fizzBuzzGenerator($max) {
        for ($i = 1; $i <= $max; $i++) {
            $output = '';
            if ($i % 3 === 0) $output .= 'Fizz';
            if ($i % 5 === 0) $output .= 'Buzz';
            yield ($output ?: $i) . "<br>";
        }
    }
}

foreach (fizzBuzzGenerator(10000) as $line) {
    echo $line;
}
CODE
    ],
    'recursive' => [
        'name' => 'Recursive',
        'complexity' => 'O(n)',
        'description' => 'Uses recursion instead of loops. Demonstrates functional programming concepts but has function call overhead. Stack depth limited, so not practical for large N.',
        'code' => <<<'CODE'
if (!function_exists('fizzBuzzRecursive')) {
    function fizzBuzzRecursive($n, $max = 10000) {
        if ($n > $max) return;
        
        $output = '';
        if ($n % 3 === 0) $output .= 'Fizz';
        if ($n % 5 === 0) $output .= 'Buzz';
        echo ($output ?: $n) . "<br>";
        
        fizzBuzzRecursive($n + 1, $max);
    }
}

fizzBuzzRecursive(1);
CODE
    ],
    'precomputed' => [
        'name' => 'Pre-computed String (Ultimate Speed)',
        'complexity' => 'O(1)',
        'description' => 'The "ultimate" optimization - pre-generate the entire output and just echo it. No logic, no loops, just raw string output. Fastest possible but defeats the purpose. Educational extreme.',
        'code' => <<<'CODE'
// Generate the pattern once (15 numbers repeat)
$pattern = '';
for ($i = 1; $i <= 15; $i++) {
    $out = '';
    if ($i % 3 === 0) $out .= 'Fizz';
    if ($i % 5 === 0) $out .= 'Buzz';
    $pattern .= ($out ?: $i) . "<br>";
}

// Repeat pattern 666 times (666 * 15 = 9990), then add remaining 10
$output = str_repeat($pattern, 666);

// Add the last 10 numbers (9991-10000)
for ($i = 9991; $i <= 10000; $i++) {
    $out = '';
    if ($i % 3 === 0) $out .= 'Fizz';
    if ($i % 5 === 0) $out .= 'Buzz';
    $output .= ($out ?: $i) . "<br>";
}

echo $output;
CODE
    ],
    'goto_loop' => [
        'name' => 'Goto Statement (Controversial)',
        'complexity' => 'O(n)',
        'description' => 'Uses PHP\'s goto for flow control. Generally considered bad practice and hard to read. Educational: demonstrates why goto is avoided in modern programming.',
        'code' => <<<'CODE'
$i = 1;

loop_start:
if ($i > 10000) goto loop_end;

$output = '';
if ($i % 3 === 0) $output .= 'Fizz';
if ($i % 5 === 0) $output .= 'Buzz';
echo ($output ?: $i) . "<br>";

$i++;
goto loop_start;

loop_end:
CODE
    ],
    'spaceship' => [
        'name' => 'Spaceship Operator (PHP 7+)',
        'complexity' => 'O(n)',
        'description' => 'Uses PHP 7\'s spaceship operator (<=>) creatively. More of a novelty than practical. Shows modern PHP features in an unconventional way.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    $fizz = ($i % 3 <=> 0) === 0;
    $buzz = ($i % 5 <=> 0) === 0;
    
    $output = '';
    if ($fizz) $output .= 'Fizz';
    if ($buzz) $output .= 'Buzz';
    
    echo ($output ?: $i) . "<br>";
}
CODE
    ],
    'array_walk' => [
        'name' => 'Array Fill + Walk',
        'complexity' => 'O(n)',
        'description' => 'Uses array_fill() and array_walk() instead of array_map. Different functional approach that modifies array in-place. Interesting comparison to the array_map version.',
        'code' => <<<'CODE'
$numbers = range(1, 10000);
$output = [];

array_walk($numbers, function($n) use (&$output) {
    $str = '';
    if ($n % 3 === 0) $str .= 'Fizz';
    if ($n % 5 === 0) $str .= 'Buzz';
    $output[] = ($str ?: $n) . "<br>";
});

echo implode('', $output);
CODE
    ],
    'math_division' => [
        'name' => 'Modulo-Free (Division)',
        'complexity' => 'O(n)',
        'description' => 'Avoids modulo by using division and floor comparison. Tests if floor($i/3) changes between consecutive numbers. Theoretical alternative but slower due to floating-point operations.',
        'code' => <<<'CODE'
for ($i = 1; $i <= 10000; $i++) {
    $output = '';
    
    // Check if we crossed a multiple of 3
    if (floor($i / 3) != floor(($i - 1) / 3)) {
        $output .= 'Fizz';
    }
    
    // Check if we crossed a multiple of 5
    if (floor($i / 5) != floor(($i - 1) / 5)) {
        $output .= 'Buzz';
    }
    
    echo ($output ?: $i) . "<br>";
}
CODE
    ]
];
