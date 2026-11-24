<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FizzBuzz\Benchmark\BenchmarkRunner;
use FizzBuzz\Benchmark\BenchmarkStatistics;
use FizzBuzz\Benchmark\FileLock;
use FizzBuzz\Http\ApiHandler;
use FizzBuzz\Strategies;
use FizzBuzz\Strategy\StrategyInterface;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function referenceFizzBuzz(int $limit): string
{
    $lines = [];
    for ($i = 1; $i <= $limit; $i++) {
        if ($i % 15 === 0) {
            $lines[] = "FizzBuzz\n";
        } elseif ($i % 3 === 0) {
            $lines[] = "Fizz\n";
        } elseif ($i % 5 === 0) {
            $lines[] = "Buzz\n";
        } else {
            $lines[] = $i . "\n";
        }
    }
    return implode('', $lines);
}

$expectedKeys = [
    'original',
    'classic',
    'clean',
    'fast',
    'ternary',
    'functional',
    'lookup',
    'string_mult',
    'switch',
    'match',
    'generator',
    'recursive',
    'precomputed',
    'goto_loop',
    'spaceship',
    'array_walk',
    'math_division',
];

$strategies = Strategies::getAll();
assertTrue(count($strategies) === 17, 'Expected 17 strategies, got ' . count($strategies));
assertTrue(array_keys($strategies) === $expectedKeys, 'Strategy keys mismatch');

$registry = Strategies::registry();
assertTrue(count($registry) === 17, 'Expected 17 StrategyInterface instances');
foreach ($registry as $key => $instance) {
    assertTrue($instance instanceof StrategyInterface, "{$key} does not implement StrategyInterface");
    assertTrue($instance->key() === $key, "Registry key mismatch for {$key}");
}

// Unit checks for BenchmarkStatistics
$summary = BenchmarkStatistics::summarize([10.0, 2.0, 4.0, 6.0]);
assertTrue(abs($summary['avg'] - 5.5) < 1e-9, 'BenchmarkStatistics avg mismatch');
assertTrue(abs($summary['median'] - 5.0) < 1e-9, 'BenchmarkStatistics even median mismatch');
assertTrue($summary['min'] === 2.0 && $summary['max'] === 10.0, 'BenchmarkStatistics min/max mismatch');
assertTrue($summary['stddev'] > 0.0, 'BenchmarkStatistics stddev expected positive');

$ranking = BenchmarkStatistics::rank([
    'a' => ['avg' => 1.0, 'median' => 1.0, 'p95' => 1.0, 'min' => 1.0, 'max' => 1.0, 'stddev' => 0.1],
    'b' => ['avg' => 1.0, 'median' => 1.0, 'p95' => 1.0, 'min' => 1.0, 'max' => 1.0, 'stddev' => 0.1],
    'c' => ['avg' => 2.0, 'median' => 2.0, 'p95' => 2.0, 'min' => 2.0, 'max' => 2.0, 'stddev' => 0.2],
]);
assertTrue($ranking['primaryRanks']['a'] === 1 && $ranking['primaryRanks']['b'] === 1, 'Expected tie rank 1 for a and b');
assertTrue($ranking['primaryRanks']['c'] === 3, 'Expected competition rank 3 after tie for c');
assertTrue($ranking['overallScores']['a'] === 5 && $ranking['overallRanks']['a'] === 1, 'Expected overallScore 5, rank 1 for a');
assertTrue($ranking['overallRanks']['c'] === 3, 'Expected overallRank 3 for c');

// Unit checks for ApiHandler
$unitHandler = new ApiHandler(new BenchmarkRunner(), new FileLock(sys_get_temp_dir() . '/fizzbuzz_unit_test.lock'));
assertTrue($unitHandler->handle('GET', [])['statusCode'] === 405, 'ApiHandler should return 405 for GET');
assertTrue($unitHandler->handle('POST', ['action' => 'run', 'limit' => '999'])['statusCode'] === 400, 'ApiHandler should reject invalid limit');
assertTrue($unitHandler->handle('POST', ['action' => 'run', 'version' => 'missing', 'limit' => '100'])['statusCode'] === 400, 'ApiHandler should reject unknown version');
assertTrue($unitHandler->handle('POST', ['action' => 'nope', 'limit' => '100'])['statusCode'] === 400, 'ApiHandler should reject unknown action');
echo "PASS: Strategy registry, BenchmarkStatistics, and ApiHandler unit checks passed.\n";

foreach ($strategies as $key => $strategy) {
    assertTrue(is_string($strategy['name']) && $strategy['name'] !== '', "Missing name for {$key}");
    assertTrue(is_string($strategy['complexity']) && $strategy['complexity'] !== '', "Missing complexity for {$key}");
    assertTrue(is_string($strategy['description']) && $strategy['description'] !== '', "Missing description for {$key}");
    assertTrue(is_string($strategy['code']) && $strategy['code'] !== '', "Missing code for {$key}");
    assertTrue(is_callable($strategy['run']), "Missing callable run for {$key}");
}

$publicData = Strategies::getPublicData();
assertTrue(count($publicData) === 17, 'Expected 17 public strategies');
foreach ($publicData as $key => $strategy) {
    assertTrue(!array_key_exists('run', $strategy), "getPublicData() leaked 'run' for {$key}");
}

foreach ([15, 100, 1000, 2000] as $limit) {
    $expectedOutput = referenceFizzBuzz($limit);
    foreach ($strategies as $key => $strategy) {
        $actual = ($strategy['run'])($limit);
        assertTrue(!str_contains($actual, '<br>'), "{$key} output contains <br> at limit={$limit}");
        assertTrue(str_ends_with($actual, "\n"), "{$key} output missing trailing newline at limit={$limit}");
        assertTrue($actual === $expectedOutput, "{$key} output mismatch at limit={$limit}");
    }
}
echo "PASS: All 17 strategies produce identical plain-text FizzBuzz output across limits [15, 100, 1000, 2000].\n";

// Code-vs-Run Parity Test: execute each displayed 'code' snippet in an isolated strict PHP subprocess
$docRoot = realpath(__DIR__ . '/..');
assertTrue($docRoot !== false, 'Failed to resolve project root');

foreach ($strategies as $key => $strategy) {
    $wrapper = "<?php\ndeclare(strict_types=1);\n\$limit = 100;\n\$fn = static function (int \$limit): string {\n" . $strategy['code'] . "\n};\necho \$fn(\$limit);\n";
    $codeProc = proc_open(
        'php',
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $codePipes,
        $docRoot
    );
    assertTrue(is_resource($codeProc), "Failed to spawn PHP subprocess for code parity check ({$key})");
    fwrite($codePipes[0], $wrapper);
    fclose($codePipes[0]);
    $stdout = stream_get_contents($codePipes[1]);
    $stderr = stream_get_contents($codePipes[2]);
    fclose($codePipes[1]);
    fclose($codePipes[2]);
    $exitCode = proc_close($codeProc);

    assertTrue($exitCode === 0, "Displayed 'code' snippet failed for {$key} (exit {$exitCode}): {$stderr}");
    assertTrue($stdout === ($strategy['run'])(100), "Displayed 'code' output differs from 'run' closure for {$key}");
}
echo "PASS: All 17 displayed 'code' snippets execute cleanly under strict_types and match 'run' closures.\n";

// End-to-end HTTP checks against api.php using a temporary PHP built-in server
$port = 8787;
$cmd = sprintf('php -S 127.0.0.1:%d -t %s', $port, escapeshellarg($docRoot));
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$proc = proc_open($cmd, $descriptors, $pipes, $docRoot);
assertTrue(is_resource($proc), 'Failed to start temporary PHP dev server');

/**
 * @return array{status: int, body: string}
 */
function httpRequest(string $url, string $method = 'GET', array $postFields = []): array
{
    $options = [
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'timeout' => 15,
        ],
    ];

    if ($method === 'POST') {
        $content = http_build_query($postFields);
        $options['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($content) . "\r\n";
        $options['http']['content'] = $content;
    }

    $context = stream_context_create($options);
    $body = file_get_contents($url, false, $context);
    assertTrue($body !== false, "HTTP request failed for {$method} {$url}");

    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $statusLine, $matches);
    $status = isset($matches[1]) ? (int) $matches[1] : 0;

    return ['status' => $status, 'body' => $body];
}

function assertBenchmarkPayload(array $benchJson, int $expectedLimit, array $expectedKeys): void
{
    assertTrue(isset($benchJson['results']) && is_array($benchJson['results']), 'Benchmark response missing results');
    assertTrue(count($benchJson['results']) === 17, 'Expected 17 results in benchmark response');

    foreach ($expectedKeys as $key) {
        assertTrue(isset($benchJson['results'][$key]), "Benchmark missing strategy {$key}");
        $r = $benchJson['results'][$key];
        foreach (['time', 'median', 'p95', 'min', 'max', 'stddev', 'memoryKb', 'runs', 'limit', 'rank', 'overallScore', 'overallRank'] as $field) {
            assertTrue(array_key_exists($field, $r), "Benchmark result for {$key} missing field {$field}");
        }
        assertTrue($r['min'] <= $r['median'] && $r['median'] <= $r['max'], "Invalid ordering min<=median<=max for {$key}");
        assertTrue($r['stddev'] >= 0, "Negative stddev for {$key}");
        assertTrue(is_numeric($r['memoryKb']) && $r['memoryKb'] >= 0, "Invalid memoryKb for {$key}");
        assertTrue($r['limit'] === $expectedLimit, "Unexpected limit for {$key}");
    }
}

try {
    $apiUrl = "http://127.0.0.1:{$port}/api.php";

    // Wait up to 3 seconds for server socket to listen
    $ready = false;
    for ($i = 0; $i < 30; $i++) {
        $sock = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);
        if ($sock !== false) {
            fclose($sock);
            $ready = true;
            break;
        }
        usleep(100_000);
    }
    assertTrue($ready, 'Temporary PHP server did not become ready in time');

    // 1. GET -> 405
    $res = httpRequest($apiUrl, 'GET');
    assertTrue($res['status'] === 405, "Expected 405 on GET, got {$res['status']}");

    // 2. Invalid action -> 400
    $res = httpRequest($apiUrl, 'POST', ['action' => 'invalid']);
    assertTrue($res['status'] === 400, "Expected 400 on invalid action, got {$res['status']}");

    // 3. Invalid version -> 400
    $res = httpRequest($apiUrl, 'POST', ['action' => 'run', 'version' => 'nonexistent', 'limit' => '100']);
    assertTrue($res['status'] === 400, "Expected 400 on unknown version, got {$res['status']}");

    // 4. Invalid limit -> 400
    $res = httpRequest($apiUrl, 'POST', ['action' => 'run', 'version' => 'classic', 'limit' => '999999']);
    assertTrue($res['status'] === 400, "Expected 400 on invalid limit, got {$res['status']}");

    // 5. Valid run (classic, limit=100) -> 200
    $res = httpRequest($apiUrl, 'POST', ['action' => 'run', 'version' => 'classic', 'limit' => '100']);
    assertTrue($res['status'] === 200, "Expected 200 on valid run, got {$res['status']}");
    $runJson = json_decode($res['body'], true);
    assertTrue(is_array($runJson), 'Run response is not valid JSON');
    assertTrue(($runJson['output'] ?? null) === referenceFizzBuzz(100), 'API run output mismatch for limit=100');
    assertTrue(isset($runJson['executionTime']) && is_numeric($runJson['executionTime']) && $runJson['executionTime'] >= 0, 'Invalid executionTime');
    assertTrue(isset($runJson['memoryKb']) && is_numeric($runJson['memoryKb']) && $runJson['memoryKb'] >= 0, 'Invalid memoryKb on run');
    assertTrue(($runJson['limit'] ?? null) === 100, 'Run response limit mismatch');

    // 6. HTTP 429 Lock Contention Test
    $lockPath = sys_get_temp_dir() . '/fizzbuzz_benchmark.lock';
    $lockHandle = fopen($lockPath, 'c');
    assertTrue($lockHandle !== false && flock($lockHandle, LOCK_EX | LOCK_NB), 'Failed to acquire test lock on ' . $lockPath);
    try {
        $res = httpRequest($apiUrl, 'POST', ['action' => 'benchmark', 'limit' => '100']);
        assertTrue($res['status'] === 429, "Expected 429 when benchmark lock is held, got {$res['status']}");
        $lockJson = json_decode($res['body'], true);
        assertTrue(is_array($lockJson) && !empty($lockJson['error']), 'Expected JSON error field on 429');
    } finally {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }

    // 7. Valid benchmark (limit=100) -> 200
    $res = httpRequest($apiUrl, 'POST', ['action' => 'benchmark', 'limit' => '100']);
    assertTrue($res['status'] === 200, "Expected 200 on valid benchmark (100), got {$res['status']}");
    $bench100 = json_decode($res['body'], true);
    assertTrue(is_array($bench100), 'Benchmark (100) response is not valid JSON');
    assertBenchmarkPayload($bench100, 100, $expectedKeys);

    // 8. Valid benchmark (limit=2000) -> 200 + wall time < 5.0s + memory differentiation
    $startNs = hrtime(true);
    $res = httpRequest($apiUrl, 'POST', ['action' => 'benchmark', 'limit' => '2000']);
    $wallSec = (hrtime(true) - $startNs) / 1_000_000_000;
    assertTrue($res['status'] === 200, "Expected 200 on valid benchmark (2000), got {$res['status']}");
    assertTrue($wallSec < 5.0, sprintf('Benchmark at limit=2000 took %.2fs (>= 5.0s)', $wallSec));
    $bench2000 = json_decode($res['body'], true);
    assertTrue(is_array($bench2000), 'Benchmark (2000) response is not valid JSON');
    assertBenchmarkPayload($bench2000, 2000, $expectedKeys);
    assertTrue(
        $bench2000['results']['functional']['memoryKb'] > $bench2000['results']['precomputed']['memoryKb'],
        'Expected functional strategy to allocate more peak memory than cached precomputed at N=2000'
    );

    echo sprintf("PASS: All api.php HTTP endpoint checks succeeded (405, 400, 429, run, benchmark N=100 & N=2000 in %.2fs).\n", $wallSec);
} finally {
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    $status = proc_get_status($proc);
    if ($status['running']) {
        proc_terminate($proc);
        if (PHP_OS_FAMILY === 'Windows' && !empty($status['pid'])) {
            @exec(sprintf('taskkill /F /T /PID %d >NUL 2>&1', (int) $status['pid']));
        }
    }
    proc_close($proc);
}
