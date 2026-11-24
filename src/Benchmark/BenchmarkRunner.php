<?php

declare(strict_types=1);

namespace FizzBuzz\Benchmark;

use FizzBuzz\Strategy\StrategyInterface;

final class BenchmarkRunner
{
    /**
     * Measure approximate peak memory allocated by a single execution.
     *
     * @param callable(int): string $runner
     */
    public function measurePeakMemoryBytes(callable $runner, int $limit): int
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

    /**
     * @return array{output: string, executionTime: float, memoryKb: float, limit: int}
     */
    public function runSingle(StrategyInterface $strategy, int $limit): array
    {
        gc_collect_cycles();
        $memBefore = memory_get_usage(false);

        if (function_exists('memory_reset_peak_usage')) {
            memory_reset_peak_usage();
            $startNs = hrtime(true);
            $output = $strategy->run($limit);
            $elapsedNs = hrtime(true) - $startNs;
            $peakBytes = max(0, memory_get_peak_usage(false) - $memBefore);
        } else {
            $startNs = hrtime(true);
            $output = $strategy->run($limit);
            $elapsedNs = hrtime(true) - $startNs;
            $peakBytes = max(0, memory_get_usage(false) - $memBefore);
        }

        return [
            'output' => $output,
            'executionTime' => round($elapsedNs / 1_000_000, 4),
            'memoryKb' => round($peakBytes / 1024, 2),
            'limit' => $limit,
        ];
    }

    /**
     * @param array<string, StrategyInterface> $strategies
     * @return array<string, array<string, mixed>>
     */
    public function runAll(array $strategies, int $limit): array
    {
        $warmupIterations = $limit <= 1000 ? 15 : 10;
        $iterations = $limit <= 1000 ? 300 : 150;
        $strategyKeys = array_keys($strategies);

        gc_collect_cycles();

        foreach ($strategyKeys as $key) {
            $strategy = $strategies[$key];
            for ($w = 0; $w < $warmupIterations; $w++) {
                $strategy->run($limit);
            }
        }

        gc_collect_cycles();

        $rawTimes = [];
        foreach ($strategyKeys as $key) {
            $rawTimes[$key] = [];
        }

        for ($round = 0; $round < $iterations; $round++) {
            $roundKeys = $strategyKeys;
            shuffle($roundKeys);

            foreach ($roundKeys as $key) {
                $strategy = $strategies[$key];
                $startNs = hrtime(true);
                $strategy->run($limit);
                $elapsedNs = hrtime(true) - $startNs;
                $rawTimes[$key][] = $elapsedNs / 1_000_000;
            }
        }

        $stats = [];
        foreach ($strategyKeys as $key) {
            $stats[$key] = BenchmarkStatistics::summarize($rawTimes[$key]);
        }

        $ranking = BenchmarkStatistics::rank($stats);

        $memoryBytes = [];
        foreach ($strategyKeys as $key) {
            $strategy = $strategies[$key];
            $memoryBytes[$key] = $this->measurePeakMemoryBytes(
                static fn (int $n): string => $strategy->run($n),
                $limit
            );
        }

        $results = [];
        foreach ($ranking['primaryKeys'] as $key) {
            $s = $stats[$key];
            $results[$key] = [
                'name' => $strategies[$key]->name(),
                'time' => round($s['avg'], 4),
                'median' => round($s['median'], 4),
                'p95' => round($s['p95'], 4),
                'min' => round($s['min'], 4),
                'max' => round($s['max'], 4),
                'stddev' => round($s['stddev'], 4),
                'memoryKb' => round($memoryBytes[$key] / 1024, 2),
                'runs' => $iterations,
                'limit' => $limit,
                'rank' => $ranking['primaryRanks'][$key],
                'overallScore' => $ranking['overallScores'][$key],
                'overallRank' => $ranking['overallRanks'][$key],
            ];
        }

        return $results;
    }
}
