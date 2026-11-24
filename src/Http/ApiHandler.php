<?php

declare(strict_types=1);

namespace FizzBuzz\Http;

use FizzBuzz\Benchmark\BenchmarkRunner;
use FizzBuzz\Benchmark\FileLock;
use FizzBuzz\Strategies;
use FizzBuzz\Strategy\StrategyInterface;

final readonly class ApiHandler
{
    /** @var array<string, StrategyInterface> */
    private array $strategies;

    /**
     * @param array<string, StrategyInterface>|null $strategies
     */
    public function __construct(
        private BenchmarkRunner $runner,
        private FileLock $lock,
        ?array $strategies = null,
    ) {
        $this->strategies = $strategies ?? Strategies::registry();
    }

    /**
     * @param array<string, mixed> $post
     * @return array{statusCode: int, payload: array<string, mixed>}
     */
    public function handle(string $method, array $post): array
    {
        if (strtoupper($method) !== 'POST') {
            return [
                'statusCode' => 405,
                'payload' => ['error' => 'Method not allowed'],
            ];
        }

        $rawLimit = $post['limit'] ?? Strategies::DEFAULT_LIMIT;
        $limit = filter_var($rawLimit, FILTER_VALIDATE_INT);
        if ($limit === false || !in_array($limit, Strategies::ALLOWED_LIMITS, true)) {
            return [
                'statusCode' => 400,
                'payload' => [
                    'error' => 'Invalid limit. Allowed values: ' . implode(', ', Strategies::ALLOWED_LIMITS),
                ],
            ];
        }

        $action = $post['action'] ?? '';

        if ($action === 'benchmark') {
            if (!$this->lock->acquire()) {
                return [
                    'statusCode' => 429,
                    'payload' => [
                        'error' => 'Another benchmark is already running. Please try again in a moment.',
                    ],
                ];
            }

            try {
                @set_time_limit(15);
                $results = $this->runner->runAll($this->strategies, $limit);
                return [
                    'statusCode' => 200,
                    'payload' => [
                        'results' => $results,
                        'limit' => $limit,
                    ],
                ];
            } finally {
                $this->lock->release();
            }
        }

        if ($action === 'run') {
            $key = $post['version'] ?? 'classic';
            if (!is_string($key) || !isset($this->strategies[$key])) {
                return [
                    'statusCode' => 400,
                    'payload' => ['error' => 'Unknown version'],
                ];
            }

            return [
                'statusCode' => 200,
                'payload' => $this->runner->runSingle($this->strategies[$key], $limit),
            ];
        }

        return [
            'statusCode' => 400,
            'payload' => ['error' => 'Invalid action'],
        ];
    }
}
