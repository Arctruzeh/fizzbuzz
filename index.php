<?php
/**
 * if a number is divisible by 3, echo fizz
 * if a number is divisible by 5, echo buzz
 * if a number is divisible by 3 and 5, echo fizzbuzz
 */

for ($x = 1; $x<=100; $x++) {
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