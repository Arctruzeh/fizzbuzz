<?php

declare(strict_types=1);

namespace FizzBuzz\Benchmark;

use InvalidArgumentException;

final class BenchmarkStatistics
{
    /**
     * @param non-empty-list<float> $times
     * @return array{avg: float, median: float, p95: float, min: float, max: float, stddev: float}
     */
    public static function summarize(array $times): array
    {
        $count = count($times);
        if ($count === 0) {
            throw new InvalidArgumentException('Cannot summarize empty timing samples.');
        }

        sort($times, SORT_NUMERIC);

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

        return [
            'avg' => $avg,
            'median' => $median,
            'p95' => $p95,
            'min' => $min,
            'max' => $max,
            'stddev' => $stddev,
        ];
    }

    /**
     * Compute per-metric competition ranks, overall scores/ranks, and median-primary order.
     *
     * @param array<string, array{avg: float, median: float, p95: float, min: float, max: float, stddev: float}> $stats
     * @return array{
     *     metricRanks: array<string, array<string, int>>,
     *     overallScores: array<string, int>,
     *     overallRanks: array<string, int>,
     *     primaryKeys: list<string>,
     *     primaryRanks: array<string, int>
     * }
     */
    public static function rank(array $stats, float $epsilon = 1e-9): array
    {
        $strategyKeys = array_keys($stats);
        $metricRanks = [];

        foreach (['avg', 'median', 'min', 'p95', 'stddev'] as $metric) {
            $sortedKeys = $strategyKeys;
            usort($sortedKeys, static fn (string $a, string $b): int => $stats[$a][$metric] <=> $stats[$b][$metric]);

            $ranks = self::assignCompetitionRanks(
                $sortedKeys,
                static fn (string $key): float => $stats[$key][$metric],
                $epsilon
            );

            foreach ($ranks as $key => $rankValue) {
                $metricRanks[$key][$metric] = $rankValue;
            }
        }

        $overallScores = [];
        foreach ($strategyKeys as $key) {
            $overallScores[$key] = array_sum($metricRanks[$key]);
        }

        $scoreSortedKeys = $strategyKeys;
        usort($scoreSortedKeys, static fn (string $a, string $b): int => $overallScores[$a] <=> $overallScores[$b]);
        $overallRanks = self::assignCompetitionRanks(
            $scoreSortedKeys,
            static fn (string $key): float => (float) $overallScores[$key],
            0.0
        );

        $primaryKeys = $strategyKeys;
        usort($primaryKeys, static function (string $a, string $b) use ($stats): int {
            $cmp = $stats[$a]['median'] <=> $stats[$b]['median'];
            return $cmp !== 0 ? $cmp : ($stats[$a]['avg'] <=> $stats[$b]['avg']);
        });

        $primaryRanks = self::assignCompetitionRanks(
            $primaryKeys,
            static fn (string $key): float => $stats[$key]['median'],
            $epsilon
        );

        return [
            'metricRanks' => $metricRanks,
            'overallScores' => $overallScores,
            'overallRanks' => $overallRanks,
            'primaryKeys' => $primaryKeys,
            'primaryRanks' => $primaryRanks,
        ];
    }

    /**
     * Assign tie-aware competition ranks (1, 1, 3) on an already-sorted list of keys.
     *
     * @param list<string> $sortedKeys
     * @param callable(string): float $valueOf
     * @return array<string, int>
     */
    private static function assignCompetitionRanks(array $sortedKeys, callable $valueOf, float $epsilon): array
    {
        $ranks = [];
        $currentRank = 1;
        $prevValue = null;

        foreach ($sortedKeys as $idx => $key) {
            $val = $valueOf($key);
            if ($prevValue !== null && ($val - $prevValue) > $epsilon) {
                $currentRank = $idx + 1;
            }
            $ranks[$key] = $currentRank;
            $prevValue = $val;
        }

        return $ranks;
    }
}
