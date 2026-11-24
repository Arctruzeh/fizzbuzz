<?php

declare(strict_types=1);

/**
 * API Handlers for FizzBuzz Benchmark
 * Handles AJAX requests for running individual strategies and benchmarking all strategies.
 */

require_once __DIR__ . '/vendor/autoload.php';

use FizzBuzz\Strategies;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawLimit = $_POST['limit'] ?? Strategies::DEFAULT_LIMIT;
$limit = filter_var($rawLimit, FILTER_VALIDATE_INT);
if ($limit === false || !in_array($limit, Strategies::ALLOWED_LIMITS, true)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid limit. Allowed values: ' . implode(', ', Strategies::ALLOWED_LIMITS),
    ]);
    exit;
}

$strategies = Strategies::getAll();
$action = $_POST['action'] ?? '';

/**
 * Measure approximate peak memory allocated by a single strategy execution.
 */
function measurePeakMemoryBytes(callable $runner, int $limit): int
{
    gc_collect_cycles();
    $memBefore = memory_get_usage(false);

    if (function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
        $out = $runner($limit);
        $peakBytes = max(0, memory_get_peak_usage(false) - $memBefore);
    } else {
        $out = $runner($limit);
        $peakBytes = max(0, memory_get_usage(false) - $memBefore);
    }

    unset($out);
    return $peakBytes;
}

if ($action === 'benchmark') {
    $lockPath = sys_get_temp_dir() . '/fizzbuzz_benchmark.lock';
    $lockHandle = fopen($lockPath, 'c');
    if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
        if ($lockHandle !== false) {
            fclose($lockHandle);
        }
        http_response_code(429);
        echo json_encode(['error' => 'Another benchmark is already running. Please try again in a moment.']);
        exit;
    }

    try {
        set_time_limit(15);

        $warmupIterations = $limit <= 1000 ? 15 : 10;
        $iterations = $limit <= 1000 ? 300 : 150;
        $strategyKeys = array_keys($strategies);

        gc_collect_cycles();

        // Warmup pass: primes OPcache/CPU and populates cached strategies (e.g., precomputed)
        foreach ($strategyKeys as $key) {
            $runner = $strategies[$key]['run'];
            for ($w = 0; $w < $warmupIterations; $w++) {
                $runner($limit);
            }
        }

        gc_collect_cycles();

        $rawTimes = [];
        foreach ($strategyKeys as $key) {
            $rawTimes[$key] = [];
        }

        // Interleave rounds across strategies to minimize sequential thermal/GC bias
        for ($round = 0; $round < $iterations; $round++) {
            $roundKeys = $strategyKeys;
            shuffle($roundKeys);

            foreach ($roundKeys as $key) {
                $runner = $strategies[$key]['run'];
                $startNs = hrtime(true);
                $runner($limit);
                $elapsedNs = hrtime(true) - $startNs;
                $rawTimes[$key][] = $elapsedNs / 1_000_000;
            }
        }

        $stats = [];
        foreach ($strategyKeys as $key) {
            $times = $rawTimes[$key];
            sort($times, SORT_NUMERIC);
            $count = count($times);

            $avg = array_sum($times) / $count;
            $mid = intdiv($count, 2);
            $median = ($count % 2 === 0) ? ($times[$mid - 1] + $times[$mid]) / 2 : $times[$mid];
            $p95Idx = (int) ceil(0.95 * $count) - 1;
            $p95 = $times[max(0, min($count - 1, $p95Idx))];
            $min = $times[0];
            $max = $times[$count - 1];

            $sumSq = 0.0;
            foreach ($times as $t) {
                $diff = $t - $avg;
                $sumSq += $diff * $diff;
            }
            $stddev = $count > 1 ? sqrt($sumSq / ($count - 1)) : 0.0;

            $stats[$key] = [
                'avg' => $avg,
                'median' => $median,
                'p95' => $p95,
                'min' => $min,
                'max' => $max,
                'stddev' => $stddev,
            ];
        }

        // Tie-aware competition ranking (1, 1, 3) on unrounded float metrics
        $eps = 1e-9;
        $metricRanks = [];
        foreach (['avg', 'median', 'min', 'p95', 'stddev'] as $metric) {
            $sortedKeys = $strategyKeys;
            usort($sortedKeys, static fn(string $a, string $b): int => $stats[$a][$metric] <=> $stats[$b][$metric]);

            $currentRank = 1;
            $prevVal = null;
            foreach ($sortedKeys as $idx => $key) {
                $val = $stats[$key][$metric];
                if ($prevVal !== null && ($val - $prevVal) > $eps) {
                    $currentRank = $idx + 1;
                }
                $metricRanks[$key][$metric] = $currentRank;
                $prevVal = $val;
            }
        }

        $overallScores = [];
        foreach ($strategyKeys as $key) {
            $overallScores[$key] = array_sum($metricRanks[$key]);
        }

        $scoreSortedKeys = $strategyKeys;
        usort($scoreSortedKeys, static fn(string $a, string $b): int => $overallScores[$a] <=> $overallScores[$b]);
        $overallRanks = [];
        $currentOverallRank = 1;
        $prevScore = null;
        foreach ($scoreSortedKeys as $idx => $key) {
            $score = $overallScores[$key];
            if ($prevScore !== null && $score > $prevScore) {
                $currentOverallRank = $idx + 1;
            }
            $overallRanks[$key] = $currentOverallRank;
            $prevScore = $score;
        }

        // Sort strategies by median (tie-breaking on avg) for primary ranking
        $primaryKeys = $strategyKeys;
        usort($primaryKeys, static function (string $a, string $b) use ($stats): int {
            $cmp = $stats[$a]['median'] <=> $stats[$b]['median'];
            return $cmp !== 0 ? $cmp : ($stats[$a]['avg'] <=> $stats[$b]['avg']);
        });

        $primaryRanks = [];
        $currentPrimaryRank = 1;
        $prevMedian = null;
        foreach ($primaryKeys as $idx => $key) {
            $med = $stats[$key]['median'];
            if ($prevMedian !== null && ($med - $prevMedian) > $eps) {
                $currentPrimaryRank = $idx + 1;
            }
            $primaryRanks[$key] = $currentPrimaryRank;
            $prevMedian = $med;
        }

        // Measure peak memory per strategy outside the timing loop
        $memoryBytes = [];
        foreach ($strategyKeys as $key) {
            $memoryBytes[$key] = measurePeakMemoryBytes($strategies[$key]['run'], $limit);
        }

        $results = [];
        foreach ($primaryKeys as $key) {
            $s = $stats[$key];
            $results[$key] = [
                'name' => $strategies[$key]['name'],
                'time' => round($s['avg'], 4),
                'median' => round($s['median'], 4),
                'p95' => round($s['p95'], 4),
                'min' => round($s['min'], 4),
                'max' => round($s['max'], 4),
                'stddev' => round($s['stddev'], 4),
                'memoryKb' => round($memoryBytes[$key] / 1024, 2),
                'runs' => $iterations,
                'limit' => $limit,
                'rank' => $primaryRanks[$key],
                'overallScore' => $overallScores[$key],
                'overallRank' => $overallRanks[$key],
            ];
        }

        echo json_encode(['results' => $results, 'limit' => $limit]);
        exit;
    } finally {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
}

if ($action === 'run') {
    $key = $_POST['version'] ?? 'classic';
    if (!is_string($key) || !isset($strategies[$key])) {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown version']);
        exit;
    }

    $runner = $strategies[$key]['run'];
    gc_collect_cycles();
    $memBefore = memory_get_usage(false);

    if (function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
        $startNs = hrtime(true);
        $output = $runner($limit);
        $elapsedNs = hrtime(true) - $startNs;
        $peakBytes = max(0, memory_get_peak_usage(false) - $memBefore);
    } else {
        $startNs = hrtime(true);
        $output = $runner($limit);
        $elapsedNs = hrtime(true) - $startNs;
        $peakBytes = max(0, memory_get_usage(false) - $memBefore);
    }

    $executionTime = $elapsedNs / 1_000_000;

    echo json_encode([
        'output' => $output,
        'executionTime' => round($executionTime, 4),
        'memoryKb' => round($peakBytes / 1024, 2),
        'limit' => $limit,
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit;
