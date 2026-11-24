<?php

declare(strict_types=1);

namespace FizzBuzz;

use FizzBuzz\Strategy\ArrayWalkStrategy;
use FizzBuzz\Strategy\ClassicStrategy;
use FizzBuzz\Strategy\CleanStrategy;
use FizzBuzz\Strategy\FastCounterStrategy;
use FizzBuzz\Strategy\FunctionalStrategy;
use FizzBuzz\Strategy\GeneratorStrategy;
use FizzBuzz\Strategy\GotoLoopStrategy;
use FizzBuzz\Strategy\LookupTableStrategy;
use FizzBuzz\Strategy\MatchStrategy;
use FizzBuzz\Strategy\MathDivisionStrategy;
use FizzBuzz\Strategy\OriginalStrategy;
use FizzBuzz\Strategy\PrecomputedStrategy;
use FizzBuzz\Strategy\RecursiveStrategy;
use FizzBuzz\Strategy\SpaceshipStrategy;
use FizzBuzz\Strategy\StrategyInterface;
use FizzBuzz\Strategy\StringMultStrategy;
use FizzBuzz\Strategy\SwitchStrategy;
use FizzBuzz\Strategy\TernaryStrategy;

/**
 * Registry facade for all FizzBuzz benchmark strategies.
 */
final class Strategies
{
    public const ALLOWED_LIMITS = [100, 500, 1000, 2000];
    public const DEFAULT_LIMIT = 1000;

    /** @var array<string, StrategyInterface>|null */
    private static ?array $instances = null;

    /**
     * @return array<string, StrategyInterface>
     */
    public static function registry(): array
    {
        if (self::$instances === null) {
            $ordered = [
                new OriginalStrategy(),
                new ClassicStrategy(),
                new CleanStrategy(),
                new FastCounterStrategy(),
                new TernaryStrategy(),
                new FunctionalStrategy(),
                new LookupTableStrategy(),
                new StringMultStrategy(),
                new SwitchStrategy(),
                new MatchStrategy(),
                new GeneratorStrategy(),
                new RecursiveStrategy(),
                new PrecomputedStrategy(),
                new GotoLoopStrategy(),
                new SpaceshipStrategy(),
                new ArrayWalkStrategy(),
                new MathDivisionStrategy(),
            ];

            $map = [];
            foreach ($ordered as $strategy) {
                $map[$strategy->key()] = $strategy;
            }
            self::$instances = $map;
        }

        return self::$instances;
    }

    public static function get(string $key): ?StrategyInterface
    {
        return self::registry()[$key] ?? null;
    }

    /**
     * @return array<string, array{name: string, complexity: string, description: string, code: string, run: callable(int): string}>
     */
    public static function getAll(): array
    {
        $all = [];
        foreach (self::registry() as $key => $strategy) {
            $all[$key] = [
                'name' => $strategy->name(),
                'complexity' => $strategy->complexity(),
                'description' => $strategy->description(),
                'code' => $strategy->code(),
                'run' => static fn (int $limit = self::DEFAULT_LIMIT): string => $strategy->run($limit),
            ];
        }

        return $all;
    }

    /**
     * @return array<string, array{name: string, complexity: string, description: string, code: string}>
     */
    public static function getPublicData(): array
    {
        $public = [];
        foreach (self::registry() as $key => $strategy) {
            $public[$key] = [
                'name' => $strategy->name(),
                'complexity' => $strategy->complexity(),
                'description' => $strategy->description(),
                'code' => $strategy->code(),
            ];
        }

        return $public;
    }
}
