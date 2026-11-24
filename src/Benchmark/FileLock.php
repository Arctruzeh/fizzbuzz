<?php

declare(strict_types=1);

namespace FizzBuzz\Benchmark;

final class FileLock
{
    private readonly string $path;

    /** @var resource|null */
    private mixed $handle = null;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? (sys_get_temp_dir() . '/fizzbuzz_benchmark.lock');
    }

    public function acquire(): bool
    {
        if ($this->handle !== null) {
            return true;
        }

        $handle = fopen($this->path, 'c');
        if ($handle === false) {
            return false;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }

        $this->handle = $handle;
        return true;
    }

    public function release(): void
    {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }

        $this->handle = null;
    }
}
