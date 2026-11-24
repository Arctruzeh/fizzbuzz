# FizzBuzz Benchmark

A high-fidelity "Industrial Assemblage" themed benchmark for FizzBuzz implementations. This project goes beyond the basic interview question to explore performance characteristics of 17 different PHP strategies.

## Features

* **Industrial Assemblage Design**: A custom aesthetic featuring "Deep Oil Black" backgrounds, CRT-style code displays, mechanical switch interactions, and stamped steel typography.
* **Performance Benchmarking**: Runs 1000 iterations of each strategy to calculate Average, Minimum, Maximum, and Variance execution times.
* **Dynamic Sorting**: Sort results by Average, Min, Max, Variance, or Overall Score.
* **Visual Feedback**: Active metrics and overall winners are highlighted in "Phosphor Green" for quick analysis.
* **17 PHP Strategies**: Original Portfolio Version, Classic Modulo, Concatenation, Counter Method, One-Liner Ternary, Functional (`array_map`), Lookup Table, String Multiplication, Switch Statement, PHP 8+ Match Expression, Generator, Recursive, Pre-computed String, Goto Loop, Spaceship Operator, Range + `array_walk`, and Modulo-Free Division.

## Tech Stack

* **Backend**: PHP 8.0+ (PSR-4 autoloaded strategies & JSON API)
* **Frontend**: HTML5, Vanilla JavaScript, Vanilla CSS (Custom Design System)
* **Design**: Custom "Industrial" theme using CSS gradients, box-shadows, and Google Fonts (`VT323`, `Space Mono`, `Share Tech Mono`).

## Local Development

Requires **PHP 8.0+** and **Composer**:

```bash
composer install
php -S localhost:8080
```

Then open [http://localhost:8080](http://localhost:8080) in your browser.

## Demo
Live Demo: https://ajlato-fizzbuzz.up.railway.app
