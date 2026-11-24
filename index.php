<?php

declare(strict_types=1);

/**
 * FizzBuzz Benchmark Application
 * Main entry point - renders the FizzBuzz Benchmark UI
 */

require_once __DIR__ . '/vendor/autoload.php';

use FizzBuzz\Strategies;

$strategies = Strategies::getPublicData();
$allowedLimits = Strategies::ALLOWED_LIMITS;
$defaultLimit = Strategies::DEFAULT_LIMIT;
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimate FizzBuzz Demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Space+Mono:ital,wght@0,400;0,700;1,400&family=VT323&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Mobile Navbar (Visible only on mobile) -->
    <header class="mobile-navbar">
        <button type="button" class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false"
            aria-controls="strategySidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <div class="navbar-brand">
            <h1>FizzBuzz<span style="color:var(--text-phosphor)">Lab</span></h1>
        </div>
    </header>

    <!-- Mobile backdrop -->
    <button type="button" class="mobile-backdrop" aria-label="Close menu"></button>

    <div class="container">
        <aside id="strategySidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Select Version</div>
                <button type="button" class="benchmark-btn" id="benchmarkBtn"
                    title="Run all versions and compare performance">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="btn-icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    Benchmark
                </button>
            </div>

            <div class="sort-controls" id="sortControls" style="display: none;">
                <button type="button" class="sort-btn" data-sort="avg">Avg</button>
                <button type="button" class="sort-btn active" data-sort="median">Med</button>
                <button type="button" class="sort-btn" data-sort="min">Min</button>
                <button type="button" class="sort-btn" data-sort="stddev">Std</button>
                <button type="button" class="sort-btn" data-sort="memory">Mem</button>
                <button type="button" class="sort-btn" data-sort="overall">All</button>
            </div>

            <ul class="version-list" id="versionList">
                <?php foreach ($strategies as $key => $data): ?>
                    <?php $escapedKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>
                    <li data-key="<?= $escapedKey ?>">
                        <button type="button" class="version-item" data-key="<?= $escapedKey ?>"
                            id="item-<?= $escapedKey ?>">
                            <span class="v-header">
                                <span class="v-name"><?= htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="v-complexity"><?= htmlspecialchars($data['complexity'], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            <span class="v-desc"><?= htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="v-time" id="time-<?= $escapedKey ?>"></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <main>
            <div class="info-header">
                <div class="info-title-row">
                    <div class="info-title-group">
                        <h2 class="info-title" id="displayTitle">Select a version</h2>
                        <span class="complexity-badge" id="displayComplexity"></span>
                    </div>

                    <div class="limit-controls">
                        <label for="limitSelect" class="limit-label">N =</label>
                        <select id="limitSelect" class="limit-select" aria-label="FizzBuzz upper limit">
                            <?php foreach ($allowedLimits as $limitOption): ?>
                                <option value="<?= (int) $limitOption ?>"<?= $limitOption === $defaultLimit ? ' selected' : '' ?>>
                                    <?= number_format((int) $limitOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mobile-strategy-nav">
                    <button type="button" class="nav-btn" id="prevStrategyBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        Prev
                    </button>
                    <button type="button" class="nav-btn" id="nextStrategyBtn">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <button type="button" class="nav-btn mobile-benchmark-btn" id="mobileBenchmarkBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    Run Benchmark (Compare All)
                </button>

                <p class="info-desc" id="displayDesc">Click a strategy on the left (or use arrows above) to inspect and
                    run the code.</p>
            </div>

            <div class="workspace">
                <!-- Code Editor Side -->
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Source Code</span>
                    </div>
                    <div class="panel-body">
                        <div class="code-content" id="codeDisplay">Select a version...</div>
                    </div>
                </div>

                <!-- Output Side -->
                <div class="panel">
                    <div class="panel-header">
                        <div class="panel-title-group">
                            <span class="panel-title">Terminal Output</span>
                            <span id="executionTime" class="execution-time"></span>
                        </div>
                        <button type="button" class="run-btn" id="runCodeBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                            </svg>
                            Run
                        </button>
                    </div>
                    <div class="panel-body">
                        <div class="output-content" id="outputDisplay">Ready to execute...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <a class="portfolio-link" href="https://ajlato.com/projects/fizzbuzz">← Back to Portfolio</a>

    <!-- Inject application config and load ES module -->
    <script id="appConfig" type="application/json"><?= json_encode([
        'strategies' => $strategies,
        'allowedLimits' => $allowedLimits,
        'defaultLimit' => $defaultLimit,
    ], $jsonFlags) ?></script>
    <script type="module" src="js/app.js"></script>

</body>

</html>
