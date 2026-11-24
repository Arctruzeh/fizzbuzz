<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use FizzBuzz\Benchmark\BenchmarkRunner;
use FizzBuzz\Benchmark\FileLock;
use FizzBuzz\Http\ApiHandler;

$handler = new ApiHandler(new BenchmarkRunner(), new FileLock());
$result = $handler->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $_POST);

header('Content-Type: application/json; charset=utf-8');
http_response_code($result['statusCode']);
echo json_encode($result['payload'], JSON_THROW_ON_ERROR);
