<?php

declare(strict_types=1);

namespace FizzBuzz\Strategy;

interface StrategyInterface
{
    public function key(): string;

    public function name(): string;

    public function complexity(): string;

    public function description(): string;

    public function code(): string;

    public function run(int $limit): string;
}
