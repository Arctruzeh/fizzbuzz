<?php
/**
 * FizzBuzz Benchmark Application
 * Main entry point - handles both API requests and page rendering
 */

// Include dependencies
require_once 'strategies.php';
require_once 'api.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimate FizzBuzz Demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600&family=Inter:wght@400;500;700&family=Share+Tech+Mono&family=Space+Mono:ital,wght@0,400;0,700;1,400&family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Mobile Navbar (Visible only on mobile) -->
<header class="mobile-navbar">
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>
    <div class="navbar-brand">
        <h1>FizzBuzz<span style="color:var(--accent-primary)">Lab</span></h1>
    </div>
</header>

<!-- Mobile backdrop -->
<div class="mobile-backdrop" onclick="toggleMobileMenu()"></div>



<div class="container">
    <aside>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Select Version</div>
            <button class="run-btn benchmark-btn" onclick="benchmarkAll()" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;" title="Run all versions and compare performance">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                Benchmark
            </button>
        </div>
        
        <div class="sort-controls" id="sortControls" style="display: none;">
            <button class="sort-btn active" onclick="sortResults('avg')" data-sort="avg">Avg</button>
            <button class="sort-btn" onclick="sortResults('min')" data-sort="min">Min</button>
            <button class="sort-btn" onclick="sortResults('max')" data-sort="max">Max</button>
            <button class="sort-btn" onclick="sortResults('variance')" data-sort="variance">Var</button>
            <button class="sort-btn" onclick="sortResults('overall')" data-sort="overall">Overall</button>
        </div>
        
        <ul class="version-list" id="versionList">
            <?php foreach ($strategies as $key => $data): ?>
            <li class="version-item" onclick="selectVersion('<?= $key ?>')" id="item-<?= $key ?>">
                <span class="v-name"><?= htmlspecialchars($data['name']) ?></span>
                <span class="v-desc"><?= htmlspecialchars($data['description']) ?></span>
                <span class="v-time" id="time-<?= $key ?>"></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main>
        <div class="info-header">
            <h2 class="info-title" id="displayTitle">Select a version</h2>
            <p class="info-desc" id="displayDesc">Click a strategy on the left to inspect and run the code.</p>
        </div>

        <div class="workspace">
            <!-- Code Editor Side -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">Source Code</span>
                </div>
                <div class="code-content" id="codeDisplay">Select a version...</div>
            </div>

            <!-- Output Side -->
            <div class="panel">
                <div class="panel-header">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span class="panel-title">Terminal Output</span>
                        <span id="executionTime" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400;"></span>
                    </div>
                    <button class="run-btn" onclick="runCode()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" /></svg>
                        Run
                    </button>
                </div>
                <div class="output-content" id="outputDisplay">Ready to execute...</div>
            </div>
        </div>
    </main>
</div>

<!-- Portfolio Link Script -->
<script data-slug="fizzbuzz">
(function() {
  var slug = document.currentScript.getAttribute("data-slug");
  var link = document.createElement("a");
  link.href = "https://ajlato.com/projects/" + slug;
  link.innerHTML = "← Back to Portfolio";
  link.style.cssText = "position: fixed; bottom: 20px; right: 20px; background: #222; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-family: system-ui, sans-serif; font-size: 14px; z-index: 10000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.2s;";
  link.onmouseover = function() { link.style.transform = "scale(1.05)"; };
  link.onmouseout = function() { link.style.transform = "scale(1)"; };
  document.body.appendChild(link);
})();
</script>

<!-- Inject strategies data for JavaScript -->
<script>
    const STRATEGIES_DATA = <?= json_encode($strategies) ?>;
</script>

<!-- Main application script -->
<script src="script.js"></script>

</body>
</html>