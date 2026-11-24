# FizzBuzz Benchmark

A high-fidelity "Industrial Assemblage" themed benchmark for FizzBuzz implementations. This project goes beyond the basic interview question to explore performance characteristics of 17 different PHP strategies.

## Features

* **Industrial Assemblage Design**: A custom aesthetic featuring "Deep Oil Black" backgrounds, CRT-style code displays, mechanical switch interactions, and stamped steel typography.
* **Rigorous Performance & Memory Benchmarking**: Uses monotonic `hrtime(true)` nanosecond timing with warmup passes and interleaved rounds across strategies to calculate Average, Median, Min, P95, Max, Sample Standard Deviation (`σ`), and Peak Memory (`KB`) without output-buffering overhead.
* **Configurable Problem Size (`N`)**: Run and benchmark strategies across `100`, `500`, `1,000`, or `2,000` numbers to observe algorithmic and memory trade-offs.
* **Dynamic Sorting & Tie-Aware Rankings**: Sort results by Average, Median, Min, StdDev, Peak Memory, or Overall Score with competition-style tie handling.
* **Visual Feedback**: Active metrics, fastest/slowest timings, and overall winners are highlighted in "Phosphor Green" for quick analysis.
* **17 PHP Strategies**: Original Portfolio Version, Classic Modulo, Concatenation, Counter Method, One-Liner Ternary, Functional (`array_map`), Lookup Table, String Multiplication, Switch Statement, PHP 8+ Match Expression, Generator, Recursive, Pre-computed String, Goto Loop, Spaceship Operator, Range + `array_walk`, and Modulo-Free Division.

## Architecture

```text
fizzbuzz/
├── src/
│   ├── Strategy/          # StrategyInterface + 17 individual strategy classes
│   ├── Benchmark/         # BenchmarkStatistics, BenchmarkRunner, FileLock
│   ├── Http/              # ApiHandler for run & benchmark requests
│   └── Strategies.php     # Thin registry facade
├── js/
│   ├── app.js             # Stateful UI controller & bootstrap
│   ├── apiClient.js       # Fetch client for api.php
│   ├── benchmarkView.js   # Sidebar rendering, badges, and sorting
│   └── highlighter.js     # Single-pass PHP syntax highlighter & ordinals
├── tests/
│   ├── verify.php         # Strategy parity, statistics, API handler & HTTP checks
│   └── frontend.test.mjs  # Native ES module unit/DOM tests
├── index.php              # Benchmark UI entrypoint
└── api.php                # Thin JSON API entrypoint
```

## Tech Stack

* **Backend**: PHP 8.0+ (PSR-4 modular Strategy pattern, BenchmarkRunner & JSON API)
* **Frontend**: HTML5, Vanilla ES Modules (`js/`), Vanilla CSS (Custom Design System)
* **Design**: Custom "Industrial" theme using CSS gradients, box-shadows, and Google Fonts (`VT323`, `Space Mono`, `Share Tech Mono`).

## Local Development

### Docker (Recommended)

Requires **Docker Desktop**:

```bash
./start.sh
# or:
docker compose up --build
```

Then open [http://localhost:8080](http://localhost:8080) in your browser.

### Native PHP

Requires **PHP 8.0+** and **Composer**:

```bash
composer install
php -S localhost:8080
```

### Automated Tests

Run both the PHP backend/parity/API suite and the Node.js frontend unit tests:

```bash
composer test
# or individually:
php tests/verify.php
node --test tests/frontend.test.mjs
```

## Demo
Live Demo: https://ajlato-fizzbuzz.up.railway.app
