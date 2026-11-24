<?php
/**
 * API Handlers for FizzBuzz Benchmark
 * Handles AJAX requests for running individual strategies and benchmarking all strategies.
 */

require_once __DIR__ . '/vendor/autoload.php';

use FizzBuzz\Strategies;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$strategies = Strategies::getAll();
$action = $_POST['action'] ?? '';

// Handle AJAX benchmark request (run all versions)
if ($action === 'benchmark') {
    $results = [];
    $iterations = 1000;

    foreach ($strategies as $key => $strategy) {
        $times = [];

        // Run each version multiple times
        for ($run = 0; $run < $iterations; $run++) {
            gc_collect_cycles();
            $startTime = microtime(true);

            ob_start();
            ($strategy['run'])();
            ob_end_clean(); // Discard output for benchmarking

            $endTime = microtime(true);
            $times[] = ($endTime - $startTime) * 1000;
        }

        // Calculate statistics
        $avgTime = array_sum($times) / count($times);
        $minTime = min($times);
        $maxTime = max($times);

        $results[$key] = [
            'name' => $strategy['name'],
            'time' => round($avgTime, 3),
            'min' => round($minTime, 3),
            'max' => round($maxTime, 3),
            'runs' => $iterations,
        ];
    }

    // Sort by time to get rankings
    uasort($results, function ($a, $b) {
        return $a['time'] <=> $b['time'];
    });

    // Add rank to each result
    $rank = 1;
    foreach ($results as $key => &$result) {
        $result['rank'] = $rank++;
    }

    echo json_encode(['results' => $results]);
    exit;
}

// Handle AJAX execution request
if ($action === 'run') {
    $key = $_POST['version'] ?? 'classic';
    if (!isset($strategies[$key])) {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown version']);
        exit;
    }

    // Reset and measure execution time
    gc_collect_cycles();
    $startTime = microtime(true);

    // Capture output
    ob_start();
    ($strategies[$key]['run'])();
    $output = ob_get_clean();

    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Return JSON response
    echo json_encode([
        'output' => $output,
        'executionTime' => round($executionTime, 3),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit;
